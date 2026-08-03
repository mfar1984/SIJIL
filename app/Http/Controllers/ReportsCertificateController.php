<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportsCertificateController extends Controller
{
    /**
     * Display a listing of certificates for reporting.
     */
    /**
     * Event ids this account may see certificates for.
     *
     * Administrators see everything; an organizer sees the events it owns.
     */
    private function scopedEventIds(): array
    {
        return Event::when(! auth()->user()->hasRole('Administrator'),
            fn ($q) => $q->where('user_id', auth()->id())
        )->pluck('id')->all();
    }

    /**
     * The certificate list for the current filters.
     *
     * Shared by the page and the export so the CSV can never disagree with what
     * is on screen.
     */
    private function filteredQuery(Request $request, array $eventIds)
    {
        $query = Certificate::query()->whereIn('event_id', $eventIds);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('participant', function ($participantQuery) use ($searchTerm) {
                    $participantQuery->where('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('event', function ($eventQuery) use ($searchTerm) {
                    $eventQuery->where('name', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhere('certificate_number', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->filled('event_filter')) {
            $query->where('event_id', $request->event_filter);
        }

        if ($request->filled('template_filter')) {
            $query->where('template_id', $request->template_filter);
        }

        // The old ranges counted forwards from today - 'week' meant "the next seven
        // days" - so they matched nothing, because certificates are issued in the
        // past. These look backwards, which is what the labels say.
        if ($request->filled('date_filter')) {
            match ($request->date_filter) {
                'today' => $query->whereDate('generated_at', now()->toDateString()),
                'week' => $query->where('generated_at', '>=', now()->subDays(7)),
                'month' => $query->where('generated_at', '>=', now()->startOfMonth()),
                'year' => $query->where('generated_at', '>=', now()->startOfYear()),
                default => null,
            };
        }

        return $query;
    }

    /**
     * Display a listing of certificates for reporting.
     */
    public function index(Request $request)
    {
        $eventIds = $this->scopedEventIds();

        $certificates = $this->filteredQuery($request, $eventIds)
            ->with(['event:id,name', 'participant:id,name,email', 'template:id,name'])
            ->orderByDesc('generated_at')
            ->paginate(\App\Support\SystemSettings::perPage($request, 10))
            ->withQueryString();

        $events = Event::whereIn('id', $eventIds)->orderBy('name')->get(['id', 'name']);

        // Only the templates this account has actually issued with. The dropdown
        // used to list every template in the system, so an organizer read the names
        // of other organizers' templates.
        $templateIds = Certificate::whereIn('event_id', $eventIds)
            ->whereNotNull('template_id')
            ->distinct()
            ->pluck('template_id');

        $templates = CertificateTemplate::whereIn('id', $templateIds)->orderBy('name')->get(['id', 'name']);

        // Real figures. The page used to show a hardcoded 98.5% "email delivery
        // rate" and a recipient count derived from it; nothing tracks certificate
        // email delivery, so both numbers were invented. These are all counted.
        $scoped = Certificate::whereIn('event_id', $eventIds);

        $totalCertificates = (clone $scoped)->count();
        $issuedThisMonth = (clone $scoped)->where('generated_at', '>=', now()->startOfMonth())->count();
        $issuedLast7Days = (clone $scoped)->where('generated_at', '>=', now()->subDays(7))->count();
        $eventsCovered = (clone $scoped)->distinct()->count('event_id');
        $missingFile = (clone $scoped)->where(function ($q) {
            $q->whereNull('pdf_file')->orWhere('pdf_file', '');
        })->count();

        // How many participants are still waiting. This is the number an organizer
        // actually wants from a certificate report.
        $participantsInScope = Participant::whereIn('event_id', $eventIds)->count();
        $participantsWithCertificate = Certificate::whereIn('event_id', $eventIds)
            ->distinct()
            ->count('participant_id');

        return view('reports.certificates', [
            'events' => $events,
            'templates' => $templates,
            'certificates' => $certificates,
            'totalCertificates' => $totalCertificates,
            'issuedThisMonth' => $issuedThisMonth,
            'issuedLast7Days' => $issuedLast7Days,
            'eventsCovered' => $eventsCovered,
            'totalEvents' => count($eventIds),
            'templatesInUse' => $templates->count(),
            'missingFile' => $missingFile,
            'participantsInScope' => $participantsInScope,
            'participantsWithCertificate' => $participantsWithCertificate,
            'participantsWaiting' => max(0, $participantsInScope - $participantsWithCertificate),
            'coverageRate' => $participantsInScope > 0
                ? round(($participantsWithCertificate / $participantsInScope) * 100, 1)
                : 0.0,
        ]);
    }

    /**
     * Export the filtered certificates as CSV.
     *
     * The button used to call alert('This feature will be implemented in a future
     * update.') while certificate_reports.export existed and was granted, so the
     * control was visible and did nothing.
     */
    public function export(Request $request)
    {
        $eventIds = $this->scopedEventIds();

        $certificates = $this->filteredQuery($request, $eventIds)
            ->with(['event:id,name', 'participant:id,name,email', 'template:id,name'])
            ->orderByDesc('generated_at')
            ->get();

        $filename = 'certificates-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($certificates) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Certificate Number', 'Participant', 'Email', 'Event',
                'Template', 'Issued At', 'File Present',
            ]);

            foreach ($certificates as $certificate) {
                fputcsv($handle, [
                    $certificate->certificate_number,
                    $certificate->participant->name ?? '',
                    $certificate->participant->email ?? '',
                    $certificate->event->name ?? '',
                    $certificate->template->name ?? '',
                    $certificate->generated_at ? $certificate->generated_at->format('Y-m-d H:i') : '',
                    $certificate->pdf_file ? 'yes' : 'no',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
    
    /**
     * Resolve a certificate the caller is allowed to see.
     *
     * The four actions below each built this themselves as
     * `where('certificate_number', $id)->orWhere('id', $id)`, an ungrouped OR that
     * combines badly with any further condition, and each then checked ownership
     * only after loading the row. Scoping the lookup itself means a certificate the
     * caller may not see simply does not resolve.
     */
    private function findCertificate($id, array $relations = []): Certificate
    {
        return Certificate::with($relations)
            ->whereIn('event_id', $this->scopedEventIds())
            ->where(function ($q) use ($id) {
                $q->where('certificate_number', $id);

                if (is_numeric($id)) {
                    $q->orWhere('id', $id);
                }
            })
            ->firstOrFail();
    }

    /**
     * Display the specified certificate.
     */
    public function show($id)
    {
        $certificate = $this->findCertificate($id, ['event', 'participant', 'template', 'generator']);

        return view('reports.certificates-show', compact('certificate'));
    }
    
    /**
     * Download the certificate PDF.
     */
    public function download($id)
    {
        $certificate = $this->findCertificate($id, ['event']);

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
        $certificate = $this->findCertificate($id, ['event']);

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
        $certificate = $this->findCertificate($id, ['participant', 'event', 'template']);

        $email = $certificate->participant->email ?? null;
        if (!$email) {
            return response()->json(['success' => false, 'message' => 'No email address available for this participant.'], 422);
        }

        // Own config first, the Administrator's as the fallback - the same rule every
        // other email in the system follows. This used to look at the caller's own
        // config only, so an organizer who never opened the delivery page could not
        // resend a certificate at all. Sendmail was also missing from the switch.
        [$config] = \App\Support\DeliveryAccount::emailConfig(auth()->id());

        if (! $config) {
            return response()->json([
                'success' => false,
                'message' => 'No email configuration for this account or the Administrator.',
            ], 422);
        }

        ['from_address' => $fromAddress, 'from_name' => $fromName] =
            \App\Support\MailerConfig::apply($config);

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