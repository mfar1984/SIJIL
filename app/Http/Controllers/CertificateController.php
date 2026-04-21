<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Event;
use App\Models\Participant;
use App\Services\CertificateNumberGenerator;
use App\Services\CertificateEncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use TCPDF;
use setasign\Fpdi\Tcpdf\Fpdi;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

class CertificateController extends Controller
{
    /**
     * Display a listing of the certificates.
     */
    public function index(Request $request)
    {
        // Query to fetch certificates with filters
        $query = Certificate::with(['event', 'participant', 'template']);
        
        // Add access control for non-admin users
        if (!auth()->user()->hasRole('Administrator')) {
            $query->whereHas('event', function($q) {
                $q->where('user_id', auth()->id());
            });
        }

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('participant', function($participantQuery) use ($searchTerm) {
                    $participantQuery->where('name', 'LIKE', "%{$searchTerm}%")
                                    ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('event', function($eventQuery) use ($searchTerm) {
                    $eventQuery->where('name', 'LIKE', "%{$searchTerm}%");
                });
            });
        }
        
        // Apply filters
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        
        if ($request->filled('template_id')) {
            $query->where('template_id', $request->template_id);
        }

        // Filter by date range
        if ($request->filled('date_filter')) {
            $today = now()->startOfDay();
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('created_at', $today->format('Y-m-d'));
                    break;
                case 'week':
                    $query->whereBetween('created_at', [$today->format('Y-m-d'), $today->addDays(7)->format('Y-m-d')]);
                    break;
                case 'month':
                    $query->whereBetween('created_at', [$today->format('Y-m-d'), $today->addMonth()->format('Y-m-d')]);
                    break;
                case 'past':
                    $query->where('created_at', '<', $today->format('Y-m-d'));
                    break;
            }
        }
        
        // Get paginated results with per_page parameter
        $perPage = $request->get('per_page', 10);
        $certificates = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Get events based on user role
        if (auth()->user()->hasRole('Administrator')) {
            $events = Event::orderBy('name')->get();
        } else {
            $events = Event::where('user_id', auth()->id())->orderBy('name')->get();
        }

        $templates = CertificateTemplate::orderBy('name')->get();
        
        return view('certificates.index', compact('events', 'templates', 'certificates'));
    }
    
    /**
     * Show the certificate generation form.
     */
    public function create()
    {
        // Get events based on user role
        if (auth()->user()->hasRole('Administrator')) {
            $events = Event::orderBy('start_date')->get(['id', 'name']);
            $templates = CertificateTemplate::all(['id', 'name']);
        } else {
            $events = Event::where('user_id', auth()->id())->orderBy('start_date')->get(['id', 'name']);
            $templates = CertificateTemplate::where('user_id', auth()->id())->get(['id', 'name']);
        }
        
        return view('certificates.create', compact('events', 'templates'));
    }
    
    /**
     * Get participants for an event (API endpoint).
     */
    public function getParticipants(Request $request)
    {
        $eventId = $request->input('event_id');
        $source = $request->input('source', 'participants'); // 'participants' or 'attendance'
        
        if (!$eventId) {
            return response()->json(['error' => 'Event ID is required'], 400);
        }
        
        // Check if user has access to this event
        if (!auth()->user()->hasRole('Administrator')) {
            $event = Event::where('id', $eventId)
                ->where('user_id', auth()->id())
                ->first();
            
            if (!$event) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }
        
        if ($source === 'attendance') {
            // Get participants from attendance records (present only)
            $participants = DB::table('participants')
                ->join('attendance_records', 'participants.id', '=', 'attendance_records.participant_id')
                ->join('attendances', 'attendance_records.attendance_id', '=', 'attendances.id')
                ->where('attendances.event_id', $eventId)
                ->where('attendance_records.status', 'present')
                ->select('participants.id', 'participants.name', 'participants.organization')
                ->distinct()
                ->get();
        } else {
            // Get all participants for the event
            $participants = Participant::where('event_id', $eventId)
                ->select('id', 'name', 'organization')
                ->get();
        }
        
        return response()->json($participants);
    }
    
    /**
     * Generate certificates for selected participants.
     */
    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'template_id' => 'required|exists:certificate_templates,id',
            'participants' => 'required|array',
            'participants.*' => 'exists:participants,id',
        ]);
        
        $eventId = $request->input('event_id');
        $templateId = $request->input('template_id');
        $participantIds = $request->input('participants');
        
        // Certificate generation started
        
        // Check if user has access to this event
        if (!auth()->user()->hasRole('Administrator')) {
            $event = Event::where('id', $eventId)
                ->where('user_id', auth()->id())
                ->first();
            
            if (!$event) {
                return back()->with('error', 'Unauthorized to generate certificates for this event');
            }
        }
        
        $event = Event::findOrFail($eventId);
        $template = CertificateTemplate::findOrFail($templateId);
        
        // Check if user has access to this template
        if (!auth()->user()->hasRole('Administrator')) {
            if ($template->user_id !== auth()->id()) {
                return back()->with('error', 'Unauthorized to use this template');
            }
        }
        
        // Found event and template
        
        $generatedCount = 0;
        $errors = [];
        
        foreach ($participantIds as $participantId) {
            try {
                $participant = Participant::findOrFail($participantId);
                
                // Processing participant
                
                // Check if certificate already exists for this event and participant
                $existingCertificate = Certificate::where('event_id', $eventId)
                    ->where('participant_id', $participantId)
                    ->first();
                
                if ($existingCertificate) {
                    // Certificate already exists - skip or regenerate?
                    // For now, we'll skip to prevent duplicates
                    $errors[] = "Certificate for {$participant->name} already exists (Cert No: {$existingCertificate->certificate_number})";
                    continue;
                }
                
                // Generate certificate number first
                $certificateNumberGenerator = app(\App\Services\CertificateNumberGenerator::class);
                $certificateNumber = $certificateNumberGenerator->generate();
                
                // Create certificate record first (so generateCertificatePDF can find it to update)
                $certificate = Certificate::create([
                    'event_id' => $eventId,
                    'participant_id' => $participantId,
                    'template_id' => $templateId,
                    'certificate_number' => $certificateNumber,
                    'pdf_file' => '', // Will be updated after PDF generation
                    'generated_at' => now(),
                    'generated_by' => Auth::id(),
                ]);
                
                // Generate PDF certificate (this will use the certificate number we generated)
                $pdfPath = $this->generateCertificatePDF($event, $participant, $template, false, $certificateNumber);
                
                // Update certificate with PDF path
                $certificate->pdf_file = $pdfPath;
                $certificate->save();
                
                // PDF generated
                
                // Certificate record created
                
                // Get global configuration for notifications
                $globalConfig = \App\Models\GlobalConfig::getConfig();
                
                Log::info('Certificate notifications check', [
                    'certificate_id' => $certificate->id,
                    'participant_id' => $participant->id,
                    'participant_phone' => $participant->phone,
                    'email_enabled' => $globalConfig->email_certificate_generated ?? false,
                    'sms_enabled' => $globalConfig->sms_certificate_generated ?? false,
                    'telegram_enabled' => $globalConfig->telegram_certificate_generated ?? false,
                ]);
                
                // Send email to participant if enabled
                if ($globalConfig && $globalConfig->email_certificate_generated) {
                    try {
                        $emailService = new \App\Services\EmailService();
                        $mailable = new \App\Mail\CertificateGeneratedNotification($event, $participant, $certificate);
                        $emailService->sendEmail($event->user_id, $mailable, $participant->email);
                    } catch (\Exception $e) {
                        Log::error('Failed to send certificate email: ' . $e->getMessage());
                    }
                }
                
                // Send SMS to participant if enabled
                if ($globalConfig && $globalConfig->sms_certificate_generated && $participant->phone) {
                    try {
                        Log::info('Attempting to send certificate SMS', [
                            'participant_id' => $participant->id,
                            'phone' => $participant->phone,
                            'event_id' => $event->id,
                            'user_id' => $event->user_id,
                        ]);
                        
                        $infobipService = new \App\Services\InfobipService();
                        $message = "Congratulations! Your certificate for {$event->name} is ready. Certificate No: {$certificate->certificate_number}. Please check your email for download instructions.";
                        $result = $infobipService->sendSms($participant->phone, $message, $event->user_id);
                        
                        Log::info('SMS send result', [
                            'success' => $result['success'] ?? false,
                            'message' => $result['message'] ?? 'No message',
                            'response' => isset($result['response']) ? json_encode($result['response']) : 'No response',
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send certificate SMS: ' . $e->getMessage(), [
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }
                
                // Send Telegram notification if enabled
                if ($globalConfig && $globalConfig->telegram_certificate_generated) {
                    try {
                        $telegramService = new \App\Services\TelegramService();
                        if ($telegramService->isEnabled()) {
                            $telegramService->sendCertificateGeneratedNotification($participant, $event, $certificate);
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to send Telegram notification: ' . $e->getMessage());
                    }
                }
                
                $generatedCount++;
            } catch (\Exception $e) {
                $errors[] = "Error generating certificate for participant ID {$participantId}: " . $e->getMessage();
                \Log::error("Error generating certificate", [
                    'participant_id' => $participantId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        // Certificate generation completed
        
        if ($generatedCount > 0) {
            $message = "{$generatedCount} certificate(s) generated successfully.";
            if (count($errors) > 0) {
                $message .= " However, there were " . count($errors) . " issue(s): " . implode('; ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= " and " . (count($errors) - 3) . " more...";
                }
            }
            return redirect()->route('certificates.index')->with('success', $message);
        } else {
            $errorMessage = 'Failed to generate any certificates.';
            if (count($errors) > 0) {
                $errorMessage .= ' Reasons: ' . implode('; ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $errorMessage .= ' and ' . (count($errors) - 3) . ' more...';
                }
            }
            return redirect()->route('certificates.index')->with('error', $errorMessage);
        }
    }
    
    /**
     * Display the specified certificate.
     */
    public function show($id)
    {
        $certificate = Certificate::with(['event', 'participant', 'template', 'generator'])->findOrFail($id);
        
        // Check if user has access to this certificate
        if (!auth()->user()->hasRole('Administrator')) {
            $event = Event::where('id', $certificate->event_id)
                ->where('user_id', auth()->id())
                ->first();
            
            if (!$event) {
                return back()->with('error', 'Unauthorized to view this certificate');
            }
        }
        
        return view('certificates.show', compact('certificate'));
    }
    
    /**
     * Preview a certificate without generating it.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'participant_id' => 'required|exists:participants,id',
            'template_id' => 'required|exists:certificate_templates,id',
        ]);
        
        $eventId = $request->input('event_id');
        $participantId = $request->input('participant_id');
        $templateId = $request->input('template_id');
        
        // Check if user has access to this event
        if (!auth()->user()->hasRole('Administrator')) {
            $event = Event::where('id', $eventId)
                ->where('user_id', auth()->id())
                ->first();
            
            if (!$event) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }
        
        $event = Event::findOrFail($eventId);
        $participant = Participant::findOrFail($participantId);
        $template = CertificateTemplate::findOrFail($templateId);
        
        // Check if user has access to this template
        if (!auth()->user()->hasRole('Administrator')) {
            if ($template->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized to use this template'], 403);
            }
        }
        
        try {
            // Generate temporary PDF certificate for preview
            $pdfPath = $this->generateCertificatePDF($event, $participant, $template, true);
            
            return response()->json([
                'success' => true,
                'preview_url' => asset('storage/' . $pdfPath),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate preview: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Generate a certificate PDF
     */
    private function generateCertificatePDF(Event $event, Participant $participant, CertificateTemplate $template, bool $isPreview = false, ?string $certificateNumber = null)
    {
        // Generate certificate number if not provided
        if (!$certificateNumber) {
            $certificateNumber = app(CertificateNumberGenerator::class)->generate();
        }
        
        // Get/normalise template PDF relative path
        // Start from pdf_file if available; normalise various formats
        $pdfRelPath = $template->pdf_file;
        if (!empty($pdfRelPath)) {
            $orig = $pdfRelPath;
            // If full URL, extract path
            if (preg_match('#https?://[^/]+(/.*)$#i', $pdfRelPath, $m)) {
                $pdfRelPath = $m[1];
            }
            // Remove leading "/" for consistency
            $pdfRelPath = ltrim($pdfRelPath, '/');
            // If points to public/storage, convert to storage relative
            if (strpos($pdfRelPath, 'storage/') === 0) {
                $pdfRelPath = substr($pdfRelPath, strlen('storage/'));
            }
            if (strpos('/'.$pdfRelPath, '/storage/') !== false) {
                $pdfRelPath = ltrim(str_replace('storage/', '', $pdfRelPath), '/');
            }
            // If contains certificate-templates path deeper, keep from there
            if (strpos($pdfRelPath, 'certificate-templates/') !== false) {
                $pdfRelPath = substr($pdfRelPath, strpos($pdfRelPath, 'certificate-templates/'));
            }
        }
        if (empty($pdfRelPath)) {
            // Try to derive from background_pdf (URL)
            $bg = $template->background_pdf;
            if ($bg) {
                // If full URL, extract path part
                if (preg_match('#https?://[^/]+(/.*)$#i', $bg, $m)) {
                    $bg = $m[1];
                }
                if (strpos($bg, '/storage/') !== false) {
                    $pdfRelPath = ltrim(str_replace('/storage/', '', $bg), '/');
                } elseif (strpos($bg, 'storage/') === 0) {
                    $pdfRelPath = substr($bg, strlen('storage/'));
                } elseif (strpos($bg, 'certificate-templates/') !== false) {
                    // Already relative under public
                    $pdfRelPath = ltrim(substr($bg, strpos($bg, 'certificate-templates/')), '/');
                }
            }
        }

        // Build initial storage path (ensure certificate-templates prefix if only filename was stored)
        if ($pdfRelPath && strpos($pdfRelPath, 'certificate-templates/') !== 0 && substr($pdfRelPath, -4) === '.pdf') {
            // Assume file is inside certificate-templates when only filename is present
            $pdfRelPath = 'certificate-templates/' . basename($pdfRelPath);
        }
        $templatePath = $pdfRelPath ? storage_path('app/public/' . $pdfRelPath) : '';
        
        // Fallbacks in case storage path is missing (different hosting setups)
        if (!$templatePath || !file_exists($templatePath)) {
            $altPaths = [
                $pdfRelPath ? public_path('storage/' . $pdfRelPath) : null, // via storage symlink
                $pdfRelPath ? public_path($pdfRelPath) : null,               // directly under public
            ];
            foreach ($altPaths as $alt) {
                if ($alt && is_string($alt) && file_exists($alt)) {
                    $templatePath = $alt;
                    break;
                }
            }
        }
        
        if (!$templatePath || !file_exists($templatePath)) {
            \Log::error("Template PDF file not found", [
                'storage_path' => $pdfRelPath ? storage_path('app/public/' . $pdfRelPath) : null,
                'public_storage' => $pdfRelPath ? public_path('storage/' . $pdfRelPath) : null,
                'public_path' => $pdfRelPath ? public_path($pdfRelPath) : null,
                'template_id' => $template->id,
                'template_pdf_file' => $template->pdf_file,
                'background_pdf' => $template->background_pdf,
            ]);
            throw new \Exception("Template PDF file not found");
        }
        
        // Create new PDF using FPDI with TCPDF
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi($template->orientation === 'portrait' ? 'P' : 'L', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('eSijil');
        $pdf->SetAuthor('eSijil Certificate System');
        $pdf->SetTitle('Certificate for ' . $participant->name);
        $pdf->SetSubject('Certificate');
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Add a page
        $pdf->AddPage();
        
        // Import the template PDF as background
        try {
            $pageCount = $pdf->setSourceFile($templatePath);
            $tplIdx = $pdf->importPage(1);
            $pdf->useTemplate($tplIdx, 0, 0, $pdf->getPageWidth(), $pdf->getPageHeight());
        } catch (\Exception $e) {
            \Log::error("Error importing template PDF", ['error' => $e->getMessage()]);
            throw new \Exception("Error importing template PDF: " . $e->getMessage());
        }
        
        /**
         * Process template elements (from new template_data) or old placeholders
         */
        if ($template->template_data && isset($template->template_data['elements']) && is_array($template->template_data['elements'])) {
            // Using new template_data format
            // Processing template_data elements
            
            // Scale factor to convert mm to points (1 mm = 2.83465 points in TCPDF)
            $mmToPointFactor = 2.83465;
            
            foreach ($template->template_data['elements'] as $element) {
                if ($element['type'] === 'text') {
                    // Get element properties
                    $x = $element['x'];
                    $y = $element['y'];
                    $fontSize = $element['fontSize'] ?? 16;
                    $fontFamily = $this->mapFontFamily($element['fontFamily'] ?? 'Arial');
                    $color = $this->hexToRgb($element['color'] ?? '#000000');
                    $style = '';
                    
                    if (isset($element['fontWeight']) && $element['fontWeight'] === 'bold') $style .= 'B';
                    if (isset($element['fontStyle']) && $element['fontStyle'] === 'italic') $style .= 'I';
                    if (isset($element['textDecoration']) && $element['textDecoration'] === 'underline') $style .= 'U';
                    
                    // Get the content and replace placeholders
                    $content = $element['content'] ?? '';
                    
                    // Process placeholders in content with format {{placeholder}}
                    $content = preg_replace_callback('/\{\{([^}]+)\}\}/', function($matches) use ($event, $participant, $certificateNumber) {
                        $placeholderType = trim($matches[1]);
                        
                        // Handle CERT-GEN placeholder
                        if ($placeholderType === 'CERT-GEN') {
                            return $certificateNumber;
                        }
                        
                        return $this->getPlaceholderText($placeholderType, $event, $participant);
                    }, $content);
                    
                    // Convert template coordinates to TCPDF points
                    // TCPDF uses top-left origin while our template may use different reference points
                    // We need to scale coordinates proportionally to the page size
                    $pageWidth = $pdf->getPageWidth();
                    $pageHeight = $pdf->getPageHeight();
                    
                    // Calculate position as percentage of template size, then apply to actual page size
                    $xPt = ($x / $template->template_data['width']) * $pageWidth;
                    $yPt = ($y / $template->template_data['height']) * $pageHeight;
                    
                    // Set font
                    $pdf->SetFont($fontFamily, $style, $fontSize);
                    
                    // Set text color
                    $pdf->SetTextColor($color['r'], $color['g'], $color['b']);
                    
                    // Adding text element to PDF
                    
                    // Make sure text is visible by ensuring it's on top of all content
                    $pdf->SetAlpha(1);
                    
                    // Set text alignment
                    $align = 'L'; // Default: Left
                    if (isset($element['textAlign'])) {
                        if ($element['textAlign'] === 'center') $align = 'C';
                        elseif ($element['textAlign'] === 'right') $align = 'R';
                    }
                    
                    // Handle text alignment with proper positioning
                    $cellWidth = 0; // Auto-width by default
                    
                    // For centered text, we need to calculate width for proper centering
                    if ($align === 'C') {
                        // For centered text, set cell width to page width 
                        // and position X at beginning of page
                        $cellWidth = $pageWidth;
                        $xPt = 0;
                    } else if ($align === 'R') {
                        // For right-aligned text, position from right edge
                        $cellWidth = $pageWidth - $xPt;
                    }
                    
                    // Add text - using Cell with explicit height for better text rendering
                    // Use ln=0 to avoid line breaks that cause page breaks
                    $pdf->SetXY($xPt, $yPt);
                    $pdf->Cell($cellWidth, 10, $content, 0, 0, $align, 0);
                    
                    // Add a debug marker to verify position
                    if ($isPreview) {
                        $pdf->SetDrawColor(255, 0, 0);
                        $pdf->Circle($xPt, $yPt, 1);
                    }
                }
                elseif ($element['type'] === 'qrcode') {
                    // Generate QR code
                    $qrCodeData = $this->generateQrCodeData($certificateNumber, $event, $participant);
                    $qrCodeImage = $this->generateQrCodeImage($qrCodeData);
                    
                    // Get element properties (in mm from designer)
                    $x = $element['x'];
                    $y = $element['y'];
                    $width = $element['width'];
                    $height = $element['height'];
                    
                    // Calculate position and size using proportional scaling
                    // Same approach as text elements
                    $pageWidth = $pdf->getPageWidth();
                    $pageHeight = $pdf->getPageHeight();
                    
                    // Calculate position as percentage of template size, then apply to actual page size
                    $xPt = ($x / $template->template_data['width']) * $pageWidth;
                    $yPt = ($y / $template->template_data['height']) * $pageHeight;
                    
                    // Calculate size as percentage of template size, then apply to actual page size
                    $widthMm = ($width / $template->template_data['width']) * $pageWidth;
                    $heightMm = ($height / $template->template_data['height']) * $pageHeight;
                    
                    // Ensure QR code stays within page boundaries with safety margin
                    // TCPDF needs extra margin to prevent page overflow
                    $safetyMargin = 20; // 20mm safety margin from bottom
                    
                    // Check if QR code would extend beyond safe zone
                    if ($yPt + $heightMm > $pageHeight - $safetyMargin) {
                        // Adjust Y position to fit within safe zone
                        $yPt = $pageHeight - $heightMm - $safetyMargin;
                        \Log::warning('QR code Y position adjusted to prevent page overflow', [
                            'original_y' => $y,
                            'adjusted_y' => $yPt,
                            'height' => $heightMm,
                            'page_height' => $pageHeight
                        ]);
                    }
                    
                    if ($xPt + $widthMm > $pageWidth) {
                        $widthMm = $pageWidth - $xPt - 5; // Leave 5mm margin from right
                    }
                    
                    // Ensure minimum size
                    $widthMm = max($widthMm, 10); // Minimum 10mm
                    $heightMm = max($heightMm, 10); // Minimum 10mm
                    
                    // Embed QR code in PDF
                    $pdf->ImageSVG('@' . $qrCodeImage, $xPt, $yPt, $widthMm, $heightMm, '', '', '', 0, false);
                }
                // Handle other element types if needed (e.g., images)
            }
        } elseif ($template->placeholders) {
            // Fall back to legacy placeholders format
            // Check if placeholders is a JSON string and decode it if needed
            $placeholders = $template->placeholders;
            if (is_string($placeholders)) {
                $placeholders = json_decode($placeholders, true);
                // Decoded placeholders from JSON string
            }
            
            // Processing legacy placeholders
            
            // Scale factor to convert mm to points (1 mm = 2.83465 points in TCPDF)
            $mmToPointFactor = 2.83465;
            
            foreach ($placeholders as $placeholder) {
                // Get placeholder properties (in mm)
                $x = $placeholder['x'];
                $y = $placeholder['y'];
                $fontSize = $placeholder['fontSize'];
                $fontFamily = $this->mapFontFamily($placeholder['fontFamily']);
                $color = $this->hexToRgb($placeholder['color']);
                $style = '';
                
                if ($placeholder['bold']) $style .= 'B';
                if ($placeholder['italic']) $style .= 'I';
                if ($placeholder['underline']) $style .= 'U';
                
                // Convert template coordinates to TCPDF points using the same approach
                $pageWidth = $pdf->getPageWidth();
                $pageHeight = $pdf->getPageHeight();
                
                // For legacy format, use A4 size as reference (210×297 mm for portrait)
                $templateWidth = $template->orientation == 'portrait' ? 210 : 297;
                $templateHeight = $template->orientation == 'portrait' ? 297 : 210;
                
                // Calculate position as percentage of template size, then apply to actual page size
                $xPt = ($x / $templateWidth) * $pageWidth;
                $yPt = ($y / $templateHeight) * $pageHeight;
                
                // Set font
                $pdf->SetFont($fontFamily, $style, $fontSize);
                
                // Set text color
                $pdf->SetTextColor($color['r'], $color['g'], $color['b']);
                
                // Get the text to display based on placeholder type
                $placeholderType = $placeholder['type'];
                // Remove {{ and }} if present
                if (strpos($placeholderType, '{{') === 0 && strpos($placeholderType, '}}') === strlen($placeholderType) - 2) {
                    $placeholderType = substr($placeholderType, 2, -2);
                } elseif (strpos($placeholderType, '{') === 0 && strpos($placeholderType, '}') === strlen($placeholderType) - 1) {
                    $placeholderType = substr($placeholderType, 1, -1);
                }
                
                $text = $this->getPlaceholderText($placeholderType, $event, $participant);
                
                // Adding placeholder to PDF
                
                // Make sure text is visible by ensuring it's on top of all content
                $pdf->SetAlpha(1);
                
                // Default alignment is left
                $align = 'L';
                
                // Handle text alignment with proper positioning
                $cellWidth = 0; // Auto-width by default
                
                // Add text - using Cell with explicit height for better text rendering
                // Use ln=0 to avoid line breaks that cause page breaks
                $pdf->SetXY($xPt, $yPt);
                $pdf->Cell($cellWidth, 10, $text, 0, 0, $align, 0);
                
                // Add a debug marker to verify position
                if ($isPreview) {
                    $pdf->SetDrawColor(255, 0, 0);
                    $pdf->Circle($xPt, $yPt, 1);
                }
            }
        } else {
            \Log::warning("No template elements or placeholders found in template", ['template_id' => $template->id]);
        }
        
        // Output the PDF
        if ($isPreview) {
            // For preview, save to temporary file
            $outputPath = storage_path('app/public/certificate-previews/');
            if (!file_exists($outputPath)) {
                mkdir($outputPath, 0755, true);
            }
            $outputFile = 'preview_' . time() . '_' . $participant->id . '.pdf';
            $pdf->Output($outputPath . $outputFile, 'F');
            return 'certificate-previews/' . $outputFile;
        } else {
            // For actual certificate, save to certificates folder
            $outputPath = storage_path('app/public/certificates/');
            if (!file_exists($outputPath)) {
                mkdir($outputPath, 0755, true);
            }
            $outputFile = 'certificate_' . time() . '_' . $participant->id . '.pdf';
            $pdf->Output($outputPath . $outputFile, 'F');
            
            return 'certificates/' . $outputFile;
        }
    }
    
    /**
     * Map font family to TCPDF font.
     */
    private function mapFontFamily($fontFamily)
    {
        $fontMap = [
            'Arial, sans-serif' => 'helvetica',
            'Arial' => 'helvetica',
            "'Times New Roman', serif" => 'times',
            'Times New Roman' => 'times',
            "'Courier New', monospace" => 'courier',
            'Courier New' => 'courier',
            'Georgia, serif' => 'times',
            'Georgia' => 'times',
            'Verdana, sans-serif' => 'helvetica',
            'Verdana' => 'helvetica',
            "'Trebuchet MS', sans-serif" => 'helvetica',
            'Trebuchet MS' => 'helvetica',
            'Tahoma' => 'helvetica',
        ];
        
        return $fontMap[$fontFamily] ?? 'helvetica';
    }
    
    /**
     * Convert hex color to RGB.
     */
    private function hexToRgb($hex)
    {
        $hex = str_replace('#', '', $hex);
        
        if (strlen($hex) === 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        
        return ['r' => $r, 'g' => $g, 'b' => $b];
    }
    
    /**
     * Get the text for a placeholder
     */
    private function getPlaceholderText($type, Event $event, Participant $participant)
    {
        // Getting placeholder text
        
        switch (strtolower(trim($type))) {
            case 'name':
            case 'participant_name':
                return $participant->name;
            case 'organization':
                return $participant->organization;
            case 'event':
            case 'event_name':
                return $event->name;
            case 'date':
            case 'event_date':
                return now()->format('d F Y');
            case 'identity_card':
                return $participant->identity_card ?? '';
            default:
                \Log::warning("Unknown placeholder type", ['type' => $type]);
                return ''; 
        }
    }

    public function destroy($id)
    {
        $certificate = Certificate::findOrFail($id);
        // Delete PDF file if exists
        if ($certificate->pdf_file && \Storage::disk('public')->exists($certificate->pdf_file)) {
            \Storage::disk('public')->delete($certificate->pdf_file);
        }
        $certificate->delete();
        return redirect()->route('certificates.index')->with('success', 'Certificate deleted successfully.');
    }

    /**
     * Generate QR code data payload
     * 
     * @param string $certificateNumber
     * @param Event $event
     * @param Participant $participant
     * @return string Encrypted QR code data
     */
    private function generateQrCodeData(string $certificateNumber, Event $event, Participant $participant): string
    {
        $data = [
            'certificate_number' => $certificateNumber,
            'participant_name' => $participant->name,
            'event_name' => $event->name,
            'event_date' => $event->start_date->format('Y-m-d'),
            'event_time' => $event->start_time ? substr($event->start_time, 0, 5) : '',
        ];
        
        $encryptionService = app(CertificateEncryptionService::class);
        return $encryptionService->encrypt($data);
    }

    /**
     * Generate QR code image (SVG)
     * 
     * @param string $data QR code data
     * @return string SVG content
     */
    private function generateQrCodeImage(string $data): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new SvgImageBackEnd()
        );
        
        $writer = new Writer($renderer);
        return $writer->writeString($data);
    }
} 