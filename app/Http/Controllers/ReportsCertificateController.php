<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportsCertificateController extends Controller
{
    /**
     * Display a listing of certificates for reporting.
     */
    public function index(Request $request)
    {
        // Query to fetch certificates with filters
        $query = Certificate::with(['event', 'participant', 'template']);
        
        // Filter by user role - administrators see all, organizers see only their events' certificates
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
                })
                ->orWhere('certificate_number', 'LIKE', "%{$searchTerm}%");
            });
        }
        
        // Apply filters
        if ($request->filled('event_filter')) {
            $query->where('event_id', $request->event_filter);
        }
        
        if ($request->filled('template_filter')) {
            $query->where('template_id', $request->template_filter);
        }

        // Filter by date range
        if ($request->filled('date_filter')) {
            $today = now()->startOfDay();
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('generated_at', $today->format('Y-m-d'));
                    break;
                case 'week':
                    $query->whereBetween('generated_at', [$today->format('Y-m-d'), $today->addDays(7)->format('Y-m-d')]);
                    break;
                case 'month':
                    $query->whereBetween('generated_at', [$today->format('Y-m-d'), $today->addMonth()->format('Y-m-d')]);
                    break;
                case 'past':
                    $query->where('generated_at', '<', $today->format('Y-m-d'));
                    break;
            }
        }
        
        // Get paginated results with per_page parameter
        $perPage = $request->get('per_page', 10);
        $certificates = $query->orderBy('generated_at', 'desc')->paginate($perPage);

        // Get events for filter dropdown based on user role
        if (auth()->user()->hasRole('Administrator')) {
            $events = Event::orderBy('name')->get();
        } else {
            $events = Event::where('user_id', auth()->id())->orderBy('name')->get();
        }
        
        $templates = CertificateTemplate::orderBy('name')->get();
        
        // Get summary statistics based on user role
        if (auth()->user()->hasRole('Administrator')) {
            $totalCertificates = Certificate::count();
            $totalTemplates = CertificateTemplate::count();
            $newTemplatesThisMonth = CertificateTemplate::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
        } else {
            // For organizers, only count certificates from their events
            $userEventIds = Event::where('user_id', auth()->id())->pluck('id')->toArray();
            $totalCertificates = Certificate::whereIn('event_id', $userEventIds)->count();
            
            // Templates might be shared across organizers, so we'll count templates used in their events
            $templateIds = Certificate::whereIn('event_id', $userEventIds)
                ->distinct('template_id')
                ->pluck('template_id')
                ->toArray();
            $totalTemplates = count($templateIds);
            
            $newTemplatesThisMonth = CertificateTemplate::whereIn('id', $templateIds)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
        }
        
        // Calculate email delivery rate (placeholder - in a real app this would be tracked)
        $emailDeliveryRate = 98.5; // Placeholder value
        $emailsDelivered = round(($totalCertificates * $emailDeliveryRate) / 100);
        
        return view('reports.certificates', compact(
            'events',
            'templates',
            'certificates',
            'totalCertificates',
            'totalTemplates',
            'newTemplatesThisMonth',
            'emailDeliveryRate',
            'emailsDelivered'
        ));
    }
    
    /**
     * Display the specified certificate.
     */
    public function show($id)
    {
        $certificate = Certificate::with(['event', 'participant', 'template', 'generator'])
            ->where('certificate_number', $id)
            ->orWhere('id', $id)
            ->firstOrFail();
        
        // Check if user has permission to view this certificate
        if (!auth()->user()->hasRole('Administrator')) {
            $event = $certificate->event;
            if (!$event || $event->user_id != auth()->id()) {
                return redirect()->route('reports.certificates')
                    ->with('error', 'You do not have permission to view this certificate.');
            }
        }
        
        return view('reports.certificates-show', compact('certificate'));
    }
    
    /**
     * Download the certificate PDF.
     */
    public function download($id)
    {
        $certificate = Certificate::with('event')
            ->where('certificate_number', $id)
            ->orWhere('id', $id)
            ->firstOrFail();
        
        // Check if user has permission to download this certificate
        if (!auth()->user()->hasRole('Administrator')) {
            $event = $certificate->event;
            if (!$event || $event->user_id != auth()->id()) {
                return redirect()->route('reports.certificates')
                    ->with('error', 'You do not have permission to download this certificate.');
            }
        }
        
        if (!$certificate->pdf_file || !Storage::disk('public')->exists($certificate->pdf_file)) {
            return back()->with('error', 'Certificate PDF file not found.');
        }
        
        $filePath = storage_path('app/public/' . $certificate->pdf_file);
        $fileName = 'Certificate_' . $certificate->certificate_number . '.pdf';
        
        return response()->download($filePath, $fileName);
    }
    
    /**
     * Delete the certificate.
     */
    public function destroy($id)
    {
        $certificate = Certificate::with('event')
            ->where('certificate_number', $id)
            ->orWhere('id', $id)
            ->firstOrFail();
        
        // Check if user has permission to delete this certificate
        if (!auth()->user()->hasRole('Administrator')) {
            $event = $certificate->event;
            if (!$event || $event->user_id != auth()->id()) {
                return redirect()->route('reports.certificates')
                    ->with('error', 'You do not have permission to delete this certificate.');
            }
        }
        
        // Delete PDF file if exists
        if ($certificate->pdf_file && Storage::disk('public')->exists($certificate->pdf_file)) {
            Storage::disk('public')->delete($certificate->pdf_file);
        }
        
        $certificate->delete();
        
        return redirect()->route('reports.certificates')->with('success', 'Certificate deleted successfully.');
    }

    /**
     * Send certificate email to participant
     */
    public function sendEmail(Request $request, $id)
    {
        $certificate = \App\Models\Certificate::with(['participant', 'event', 'template'])
            ->where('certificate_number', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        // Check permission
        if (!auth()->user()->hasRole('Administrator')) {
            $event = $certificate->event;
            if (!$event || $event->user_id != auth()->id()) {
                return response()->json(['success' => false, 'message' => 'You do not have permission to send this certificate.'], 403);
            }
        }

        $email = $certificate->participant->email ?? null;
        if (!$email) {
            return response()->json(['success' => false, 'message' => 'No email address available for this participant.'], 422);
        }

        // Ambil config delivery aktif untuk user ini
        $userId = auth()->id();
        $config = \App\Models\DeliveryConfig::where('user_id', $userId)
            ->where('config_type', 'email')
            ->where('is_active', true)
            ->first();
        if (!$config) {
            return response()->json(['success' => false, 'message' => 'No active email delivery configuration found.'], 422);
        }
        $settings = $config->settings;
        $provider = $config->provider;
        $fromName = $settings['from_name'] ?? 'SIJIL System';
        $fromAddress = $settings['from_address'] ?? 'no-reply@example.com';

        // Set konfigurasi mail dinamis
        switch ($provider) {
            case 'smtp':
                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.host' => $settings['host'] ?? 'smtp.mailtrap.io',
                    'mail.mailers.smtp.port' => $settings['port'] ?? '2525',
                    'mail.mailers.smtp.encryption' => $settings['encryption'] === 'none' ? null : $settings['encryption'],
                    'mail.mailers.smtp.username' => $settings['username'] ?? '',
                    'mail.mailers.smtp.password' => $settings['password'] ?? '',
                    'mail.from.address' => $fromAddress,
                    'mail.from.name' => $fromName,
                ]);
                break;
            case 'mailgun':
                config([
                    'mail.default' => 'mailgun',
                    'services.mailgun.domain' => $settings['domain'] ?? '',
                    'services.mailgun.secret' => $settings['secret'] ?? '',
                    'services.mailgun.endpoint' => $settings['endpoint'] ?? 'api.mailgun.net',
                    'mail.from.address' => $fromAddress,
                    'mail.from.name' => $fromName,
                ]);
                break;
            case 'ses':
                config([
                    'mail.default' => 'ses',
                    'services.ses.key' => $settings['key'] ?? '',
                    'services.ses.secret' => $settings['secret'] ?? '',
                    'services.ses.region' => $settings['region'] ?? 'us-east-1',
                    'mail.from.address' => $fromAddress,
                    'mail.from.name' => $fromName,
                ]);
                break;
        }

        // Compose email
        $event = $certificate->event;
        $participant = $certificate->participant;
        $user = auth()->user();
        $subject = 'Your Certificate from ' . ($event->name ?? 'SIJIL System');

        // Format tanggal dan waktu
        $date = $event->start_date ? date('d M Y', strtotime($event->start_date)) : '';
        if ($event->end_date && $event->end_date !== $event->start_date) {
            $date .= ' to ' . date('d M Y', strtotime($event->end_date));
        }
        $time = $event->start_time ? substr($event->start_time, 0, 5) : '';
        if ($event->end_time && $event->end_time !== $event->start_time) {
            $time .= ' to ' . substr($event->end_time, 0, 5);
        }
        $location = $event->location ?? '-';

        $body = "Dear {$participant->name},\n\n" .
            "We would like to extend our warmest congratulations on your successful completion of the program detailed below. Attached, you will find your certificate.\n\n" .
            "Program Name: {$event->name}\n" .
            "Date: {$date}\n" .
            "Time: {$time}\n" .
            "Location: {$location}\n\n" .
            "We wish you all the best in your future endeavors.\n\n" .
            "Kind regards,\n" .
            "{$user->name}\n" .
            ($user->phone ?? '');

        try {
            \Mail::raw($body, function ($mail) use ($email, $subject, $fromName, $fromAddress, $certificate) {
                $mail->to($email)
                    ->subject($subject)
                    ->from($fromAddress, $fromName);
                // Attach PDF if available
                if ($certificate->pdf_file && \Storage::disk('public')->exists($certificate->pdf_file)) {
                    $mail->attach(storage_path('app/public/' . $certificate->pdf_file), [
                        'as' => 'Certificate_' . $certificate->certificate_number . '.pdf',
                        'mime' => 'application/pdf',
                    ]);
                }
            });
            return response()->json(['success' => true, 'message' => 'Email sent successfully to ' . $email]);
        } catch (\Exception $e) {
            \Log::error('Certificate email sending error: ' . $e->getMessage(), [
                'exception' => $e,
                'certificate_id' => $certificate->id,
                'toEmail' => $email,
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()]);
        }
    }
} 