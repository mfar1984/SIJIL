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
        // Query to fetch certificates with filters.
        // withTrashed() on the relations: a certificate is an official record, so
        // it must keep showing its event and participant even after those have
        // been moved to the Recycle Bin.
        $query = Certificate::with([
            'event' => fn($q) => $q->withTrashed(),
            'participant' => fn($q) => $q->withTrashed(),
            'template' => fn($q) => $q->withTrashed(),
        ]);
        
        // Role-based access control
        // Administrator: can see ALL certificates from ALL organizers
        // Organizer: can ONLY see certificates from their own events
        if (!auth()->user()->hasRole('Administrator')) {
            $query->whereHas('event', function($q) {
                $q->withTrashed()->where('user_id', auth()->id());
            });
        }

        // Filter by registration type (tab)
        // Filters certificates based on participant's registration type
        // 'verified' tab: certificates for participants with IC/Passport
        // 'simplified' tab: certificates for participants without IC/Passport (Quick Registration)
        $tab = $request->get('tab', 'verified') === 'simplified' ? 'simplified' : 'verified';
        $query->whereHas('participant', function($q) use ($tab) {
            $q->withTrashed()->where('registration_type', $tab);
        });

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

        // Filter by date range.
        // These windows look backwards: certificates are generated in the past,
        // so the previous forward-looking ranges never matched anything.
        if ($request->filled('date_filter')) {
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('created_at', now()->toDateString());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'past':
                    $query->where('created_at', '<', now()->startOfDay());
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
        
        return view('certificates.index', [
            'events' => $events,
            'templates' => $templates,
            'certificates' => $certificates,
            'activeTab' => $tab
        ]);
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
            // Exclude participants who already have certificates for this event
            // Raw query builder ignores soft deletes, so deleted_at is filtered
            // by hand: participants and attendance sessions sitting in the
            // Recycle Bin must not appear, while a certificate in the Recycle
            // Bin should no longer block a participant from being issued again.
            $participants = DB::table('participants')
                ->join('attendance_records', 'participants.id', '=', 'attendance_records.participant_id')
                ->join('attendances', 'attendance_records.attendance_id', '=', 'attendances.id')
                ->leftJoin('certificates', function($join) use ($eventId) {
                    $join->on('participants.id', '=', 'certificates.participant_id')
                         ->where('certificates.event_id', '=', $eventId)
                         ->whereNull('certificates.deleted_at');
                })
                ->where('attendances.event_id', $eventId)
                ->where('attendance_records.status', 'present')
                ->whereNull('participants.deleted_at')
                ->whereNull('attendances.deleted_at')
                ->whereNull('certificates.id') // Exclude participants who already have certificates
                ->select('participants.id', 'participants.name', 'participants.organization', 'participants.registration_type')
                ->distinct()
                ->get();
        } else {
            // Get all participants for the event
            // Exclude participants who already have certificates for this event
            $participants = Participant::where('event_id', $eventId)
                ->whereNotExists(function($query) use ($eventId) {
                    $query->select(DB::raw(1))
                          ->from('certificates')
                          ->whereColumn('certificates.participant_id', 'participants.id')
                          ->where('certificates.event_id', $eventId)
                          ->whereNull('certificates.deleted_at');
                })
                ->select('id', 'name', 'organization', 'registration_type')
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
                // Use different email template based on participant's registration type
                if ($globalConfig && $globalConfig->email_certificate_generated) {
                    try {
                        $emailService = new \App\Services\EmailService();
                        
                        // Simplified participants get direct download link
                        // Verified participants get PWA portal link
                        if ($participant->registration_type === 'simplified') {
                            $mailable = new \App\Mail\CertificateGeneratedSimplified($event, $participant, $certificate);
                        } else {
                            $mailable = new \App\Mail\CertificateGeneratedNotification($event, $participant, $certificate);
                        }
                        
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
     * Download certificate for simplified participants (public route with signed URL)
     * This allows simplified participants to download their certificate directly
     * without needing to access the PWA portal (since they don't have IC/Passport)
     */
    public function downloadSimplified($id)
    {
        $certificate = Certificate::with(['event', 'participant'])->findOrFail($id);
        
        // Verify this is a simplified participant
        if ($certificate->participant->registration_type !== 'simplified') {
            abort(403, 'This download link is only for simplified registration participants');
        }
        
        // Get the PDF file path
        $pdfPath = storage_path('app/public/' . $certificate->pdf_file);
        
        if (!file_exists($pdfPath)) {
            abort(404, 'Certificate file not found');
        }
        
        // Return the PDF file for download
        return response()->download($pdfPath, 'certificate-' . $certificate->certificate_number . '.pdf');
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
    /**
     * Render the certificate PDF and return its path relative to the public disk.
     *
     * Public rather than private because CertificateIssuer needs it to issue a
     * certificate automatically on registration. Copying this method would mean
     * maintaining the layout, font and coordinate handling in two places, and
     * the two copies would drift.
     */
    public function generateCertificatePDF(Event $event, Participant $participant, CertificateTemplate $template, bool $isPreview = false, ?string $certificateNumber = null)
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

        // A certificate is a fixed canvas, so every default that shifts content
        // has to go. Without these, TCPDF applied 15 mm page margins, 1.5 mm of
        // cell padding on each side, and would push anything near the bottom
        // onto a second page.
        $pdf->SetMargins(0, 0, 0, true);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->setCellPaddings(0, 0, 0, 0);
        $pdf->setCellMargins(0, 0, 0, 0);
        $pdf->setCellHeightRatio(\App\Support\CertificateLayout::LINE_HEIGHT_RATIO);
        
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
                    
                    // Debug logging
                    \Log::info("Rendering text element", [
                        'content' => $content,
                        'font' => $fontFamily,
                        'size' => $fontSize,
                        'color' => $color,
                        'position' => ['x' => $xPt, 'y' => $yPt],
                        'align' => isset($element['textAlign']) ? $element['textAlign'] : 'L'
                    ]);
                    
                    // Set font. Custom fonts live outside TCPDF's own folder, so
                    // the definition file is passed explicitly; resolve() falls
                    // back to a built-in font when the file is not installed.
                    [$resolvedFamily, $resolvedFile] = \App\Support\CertificateFonts::resolve($fontFamily);
                    $pdf->SetFont($resolvedFamily, $style, $fontSize, $resolvedFile);
                    
                    // Set text color
                    $pdf->SetTextColor($color['r'], $color['g'], $color['b']);
                    
                    // Make sure text is visible by ensuring it's on top of all content
                    $pdf->SetAlpha(1);

                    // x is an anchor, not always the left edge: measure the text
                    // and shift left so centre and right alignment land on the
                    // exact spot chosen in the designer. Previously centred text
                    // ignored x and centred on the whole page, and right aligned
                    // text was pinned to the page edge.
                    $align = \App\Support\CertificateLayout::normaliseAlign($element['textAlign'] ?? 'left');
                    $textWidth = $pdf->GetStringWidth($content);
                    $drawX = \App\Support\CertificateLayout::drawX($xPt, $textWidth, $align);

                    // Cell height 0 means "one line", and calign/valign 'T'
                    // makes yPt the top of the text instead of the middle of a
                    // fixed 10 mm box, which used to drop every element by 5 mm.
                    $pdf->SetXY($drawX, $yPt);
                    $pdf->Cell($textWidth, 0, $content, 0, 0, 'L', false, '', 0, false, 'T', 'T');
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
                    
                    // Keep the QR code on the page without moving it away from
                    // where it was placed. Auto page break is off now, so the
                    // old 20 mm bottom "safety margin" is no longer needed and
                    // only served to silently jump the code upwards.
                    $widthMm = max($widthMm, 10);  // Minimum 10mm
                    $heightMm = max($heightMm, 10); // Minimum 10mm

                    $xPt = max(0, min($xPt, $pageWidth - $widthMm));
                    $yPt = max(0, min($yPt, $pageHeight - $heightMm));
                    
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
                
                // Set font (see the note on the other SetFont call above)
                [$resolvedFamily, $resolvedFile] = \App\Support\CertificateFonts::resolve($fontFamily);
                $pdf->SetFont($resolvedFamily, $style, $fontSize, $resolvedFile);
                
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
                
                // Legacy placeholders are always left aligned. Same anchoring
                // rule as the modern elements: yPt is the top of the text.
                $pdf->SetXY($xPt, $yPt);
                $pdf->Cell($pdf->GetStringWidth($text), 0, $text, 0, 0, 'L', false, '', 0, false, 'T', 'T');
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
            // Custom handwriting fonts. These are not part of TCPDF, so
            // CertificateFonts::resolve() supplies the definition file and
            // falls back to Helvetica if it has not been installed.
            'Amsterdam' => 'amsterdam',
            'Dancing Script' => 'dancingscript',
            'Pacifico' => 'pacifico',
            'Great Vibes' => 'greatvibes',
            'Allura' => 'allura',
            'Sacramento' => 'sacramento',
        ];

        // Match on the raw value first, then case-insensitively, so a template
        // storing "amsterdam" or "AMSTERDAM" resolves the same way.
        if (isset($fontMap[$fontFamily])) {
            return $fontMap[$fontFamily];
        }

        foreach ($fontMap as $name => $tcpdfName) {
            if (strcasecmp($name, (string) $fontFamily) === 0) {
                return $tcpdfName;
            }
        }

        return \App\Support\CertificateFonts::isCustom((string) $fontFamily)
            ? strtolower((string) $fontFamily)
            : 'helvetica';
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
     * Convert text to title case (capitalize first letter of each word)
     * Handles Malaysian names properly (Bin, Binti, etc.)
     */
    private function toTitleCase($text)
    {
        if (empty($text)) {
            return $text;
        }
        
        // Convert to lowercase first
        $text = mb_strtolower($text, 'UTF-8');
        
        // Split by spaces
        $words = explode(' ', $text);
        
        // Capitalize first letter of each word
        $words = array_map(function($word) {
            return mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($word, 1, null, 'UTF-8');
        }, $words);
        
        return implode(' ', $words);
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
                // Auto-capitalize name (Title Case)
                return $this->toTitleCase($participant->name);
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

        // Soft delete only. The PDF is kept on disk so a restore from the
        // Recycle Bin returns a working certificate; the file is removed only
        // when the record is permanently deleted from the bin.
        $certificate->delete();

        return redirect()->route('certificates.index')->with(
            'success',
            'Certificate moved to Recycle Bin. It can no longer be verified until restored from Settings → Global Config → Recycle Bin.'
        );
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