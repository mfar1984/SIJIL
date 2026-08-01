<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use TCPDF;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class EventManagementController extends Controller
{
    /**
     * Whether the current user may act on the given event.
     *
     * Administrators may act on any event; everyone else is limited to events
     * they created.
     */
    private function canManage(Event $event): bool
    {
        return auth()->user()->hasRole('Administrator') || $event->user_id == auth()->id();
    }

    /**
     * Delete a poster file from the public disk, given the stored relative path.
     */
    private function deletePoster(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }

    /**
     * Display the event management page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Only the participant count is rendered, so avoid loading every
        // participant row for every event on the page.
        $query = Event::withCount('participants');

        // For non-Administrator users, filter by their events
        if (!auth()->user()->hasRole('Administrator')) {
            $query->where('user_id', auth()->id());
        }

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('organizer', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('location', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_filter')) {
            $today = now()->startOfDay();
            switch ($request->date_filter) {
                case 'today':
                    $query->where('start_date', $today->format('Y-m-d'));
                    break;
                case 'week':
                    $query->whereBetween('start_date', [$today->format('Y-m-d'), $today->addDays(7)->format('Y-m-d')]);
                    break;
                case 'month':
                    $query->whereBetween('start_date', [$today->format('Y-m-d'), $today->addMonth()->format('Y-m-d')]);
                    break;
                case 'past':
                    $query->where('start_date', '<', $today->format('Y-m-d'));
                    break;
            }
        }

        // Get paginated results with per_page parameter
        $perPage = $request->get('per_page', 10);
        $events = $query->orderBy('start_date', 'desc')->paginate($perPage);

        return view('event-management', [
            'events' => $events
        ]);
    }
    
    /**
     * Show the form for creating a new event.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('events.create', [
            'certificateTemplates' => $this->availableTemplates(),
        ]);
    }

    /**
     * Create the attendance session that was set up on the event form.
     *
     * Doing it here means the operator sets the event and its scan times in one
     * pass. The standalone attendance form still exists for changing them later,
     * and both paths keep the one-session-per-event rule.
     *
     * @return string  a note for the flash message, empty when nothing was created
     */
    private function createAttendanceFromRequest(Request $request, Event $event): string
    {
        if (!$event->attendance_required) {
            return '';
        }

        // One setup per event; never a second.
        if ($event->attendances()->exists()) {
            return '';
        }

        $sessions = $request->input('attendance_sessions', []);

        if (!is_array($sessions) || $sessions === []) {
            return ' Attendance is on but no scan times were set, so no QR code exists yet.';
        }

        // Drop anything without a date rather than storing a broken session.
        $sessions = array_values(array_filter(
            $sessions,
            fn ($s) => is_array($s) && !empty($s['date']) && !empty($s['checkin_start_time'])
        ));

        if ($sessions === []) {
            return ' Attendance is on but no scan times were set, so no QR code exists yet.';
        }

        $type = $request->input('attendance_type', 'single');

        if (!in_array($type, ['single', 'daily', 'custom'], true)) {
            $type = 'single';
        }

        // Check-out is only offered in the manual mode.
        $wantsCheckout = $type === 'custom' && $request->boolean('attendance_enable_checkout');

        $first = $sessions[0];

        $attendance = \App\Models\Attendance::create([
            'event_id' => $event->id,
            'status' => 'active',
            'attendance_type' => $type,
            'unique_code' => \Illuminate\Support\Str::random(32),
            'created_by' => auth()->id(),
            'date' => $first['date'],
            'start_time' => $first['checkin_start_time'],
            'end_time' => $first['checkin_end_time'] ?? $first['checkin_start_time'],
        ]);

        $codes = 0;

        foreach ($sessions as $session) {
            $attendance->sessions()->create([
                'unique_code' => \Illuminate\Support\Str::random(32),
                'session_type' => 'checkin',
                'date' => $session['date'],
                'checkin_start_time' => $session['checkin_start_time'],
                'checkin_end_time' => $session['checkin_end_time'] ?? $session['checkin_start_time'],
                'checkout_start_time' => null,
                'checkout_end_time' => null,
            ]);
            $codes++;

            if ($wantsCheckout && !empty($session['checkout_start_time'])) {
                $attendance->sessions()->create([
                    'unique_code' => \Illuminate\Support\Str::random(32),
                    'session_type' => 'checkout',
                    'date' => $session['date'],
                    'checkin_start_time' => null,
                    'checkin_end_time' => null,
                    'checkout_start_time' => $session['checkout_start_time'],
                    'checkout_end_time' => $session['checkout_end_time'] ?? $session['checkout_start_time'],
                ]);
                $codes++;
            }
        }

        return " Attendance set up with {$codes} QR code" . ($codes === 1 ? '' : 's') . '.';
    }

    /**
     * Templates this user may pick for automatic certificate issuing.
     * Administrators see every active template; an organizer sees their own.
     */
    private function availableTemplates()
    {
        $user = auth()->user();

        return \App\Models\CertificateTemplate::where('is_active', true)
            ->when(
                $user && !$user->hasRole('Administrator'),
                fn ($q) => $q->where('user_id', $user->id)
            )
            ->orderBy('name')
            ->get(['id', 'name']);
    }
    
    /**
     * Store a newly created event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'organizer' => 'required|string|max:255',
            'description' => 'nullable|string',
            'condition' => 'nullable|string',
            'start_date' => 'required|date',
            'start_time' => 'required',
            'end_date' => 'required|date|after_or_equal:start_date',
            'end_time' => 'required',
            'location' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:30',
            'max_participants' => 'required|integer|min:1',
            'status' => 'required|in:active,pending,completed',
            'poster' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'disable_auto_expiry' => 'nullable|boolean',
            'skip_identity_verification' => 'nullable|boolean',
            'auto_pwa_registration' => 'nullable|boolean',
            'auto_generate_certificate' => 'nullable|boolean',
            'attendance_required' => 'nullable|boolean',
            'certificate_template_id' => 'nullable|exists:certificate_templates,id',
        ]);
        
        // Create a new event
        $event = new Event();
        $event->name = $request->name;
        $event->organizer = $request->organizer;
        $event->description = $request->description;
        $event->condition = $request->condition;
        $event->start_date = $request->start_date;
        $event->start_time = $request->start_time;
        $event->end_date = $request->end_date;
        $event->end_time = $request->end_time;
        $event->location = $request->location;
        $event->address = $request->address;
        $event->max_participants = $request->max_participants;
        $event->status = $request->status;
        $event->user_id = auth()->id();
        $event->contact_person = $request->contact_person;
        $event->contact_email = $request->contact_email;
        $event->contact_phone = $request->contact_phone;
        $event->disable_auto_expiry = $request->boolean('disable_auto_expiry');
        $event->skip_identity_verification = $request->boolean('skip_identity_verification');
        $event->auto_pwa_registration = $request->boolean('auto_pwa_registration');
        $event->auto_generate_certificate = $request->boolean('auto_generate_certificate');
        $event->attendance_required = $request->boolean('attendance_required');
        // Only meaningful when certificates are issued automatically; clearing it
        // otherwise keeps the stored data honest.
        $event->certificate_template_id = $request->boolean('auto_generate_certificate')
            ? $request->input('certificate_template_id')
            : null;
        
        // Handle poster upload
        if ($request->hasFile('poster')) {
            $path = $request->file('poster')->store('events/posters', 'public');
            $event->poster = $path;
        }
        
        // Save first to get ID
        $event->save();
        
        // Generate the registration link and save again
        $event->generateRegistrationLink();
        $event->save();

        // Scan times were set on the same form, so create them now.
        $attendanceNote = $this->createAttendanceFromRequest($request, $event);

        return redirect()->route('event.management')
            ->with('success', 'Event created successfully!' . $attendanceNote);
    }
    
    /**
     * Display the specified event.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Find the event from database
        $event = Event::find($id);
        
        // If event not found, redirect with error
        if (!$event) {
            return redirect()->route('event.management')
                ->with('error', 'Event not found.');
        }
        
        // Check if user has permission to view this event
        if (!$this->canManage($event)) {
            return redirect()->route('event.management')
                ->with('error', 'You do not have permission to view this event.');
        }
        
        return view('events.show', [
            'event' => $event
        ]);
    }
    
    /**
     * Show the form for editing the specified event.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        // Find the event from database
        $event = Event::find($id);
        
        // If event not found, redirect with error
        if (!$event) {
            return redirect()->route('event.management')
                ->with('error', 'Event not found.');
        }
        
        // Check if user has permission to edit this event
        if (!$this->canManage($event)) {
            return redirect()->route('event.management')
                ->with('error', 'You do not have permission to edit this event.');
        }
        
        // Add formatted date and time fields for the form
        $event->start_date_formatted = $event->start_date ? $event->start_date->format('Y-m-d') : null;
        $event->end_date_formatted = $event->end_date ? $event->end_date->format('Y-m-d') : null;
        $event->start_time_formatted = $event->start_time;
        $event->end_time_formatted = $event->end_time;
        
        return view('events.edit', [
            'event' => $event,
            'certificateTemplates' => $this->availableTemplates(),
        ]);
    }
    
    /**
     * Update the specified event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'organizer' => 'required|string|max:255',
            'description' => 'nullable|string',
            'condition' => 'nullable|string',
            'start_date' => 'required|date',
            'start_time' => 'required',
            'end_date' => 'required|date|after_or_equal:start_date',
            'end_time' => 'required',
            'location' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:30',
            'max_participants' => 'required|integer|min:1',
            'status' => 'required|in:active,pending,completed',
            'poster' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'disable_auto_expiry' => 'nullable|boolean',
            'skip_identity_verification' => 'nullable|boolean',
            'auto_pwa_registration' => 'nullable|boolean',
            'auto_generate_certificate' => 'nullable|boolean',
            'attendance_required' => 'nullable|boolean',
            'certificate_template_id' => 'nullable|exists:certificate_templates,id',
        ]);
        
        // Find the event
        $event = Event::find($id);
        
        // If event not found, redirect with error
        if (!$event) {
            return redirect()->route('event.management')
                ->with('error', 'Event not found.');
        }
        
        // Check if user has permission to update this event
        if (!$this->canManage($event)) {
            return redirect()->route('event.management')
                ->with('error', 'You do not have permission to update this event.');
        }
        
        // Update the event
        $event->name = $request->name;
        $event->organizer = $request->organizer;
        $event->description = $request->description;
        $event->condition = $request->condition;
        $event->start_date = $request->start_date;
        $event->start_time = $request->start_time;
        $event->end_date = $request->end_date;
        $event->end_time = $request->end_time;
        $event->location = $request->location;
        $event->address = $request->address;
        $event->max_participants = $request->max_participants;
        $event->status = $request->status;
        $event->contact_person = $request->contact_person;
        $event->contact_email = $request->contact_email;
        $event->contact_phone = $request->contact_phone;
        $event->disable_auto_expiry = $request->boolean('disable_auto_expiry');
        $event->skip_identity_verification = $request->boolean('skip_identity_verification');
        $event->auto_pwa_registration = $request->boolean('auto_pwa_registration');
        $event->auto_generate_certificate = $request->boolean('auto_generate_certificate');
        $event->attendance_required = $request->boolean('attendance_required');
        $event->certificate_template_id = $request->boolean('auto_generate_certificate')
            ? $request->input('certificate_template_id')
            : null;
        
        // Handle poster upload (replace existing)
        if ($request->hasFile('poster')) {
            $previousPoster = $event->poster;
            $event->poster = $request->file('poster')->store('events/posters', 'public');
            
            // Only remove the old file once the replacement is in place
            $this->deletePoster($previousPoster);
        }
        
        // Save the event
        $event->save();

        // Attendance may have just been switched on, with the scan times set on
        // this same form. Existing setups are left alone: changing those belongs
        // on the attendance form, which owns the QR codes.
        $attendanceNote = $this->createAttendanceFromRequest($request, $event);
        
        return redirect()->route('event.management')
            ->with('success', 'Event updated successfully!' . $attendanceNote);
    }
    
    /**
     * Remove the specified event from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Find the event
        $event = Event::find($id);
        
        if (!$event) {
            return redirect()->route('event.management')
                ->with('error', 'Event not found.');
        }
        
        // Check if user has permission to delete this event
        if (!$this->canManage($event)) {
            return redirect()->route('event.management')
                ->with('error', 'You do not have permission to delete this event.');
        }
        
        // Soft delete keeps the row in the database, so the ON DELETE CASCADE
        // foreign keys never fire: participants, attendance records and issued
        // certificates all stay intact. The poster is kept too, so a restore
        // from the Recycle Bin brings the event back complete.
        $certificateCount = $event->certificates()->count();
        $participantCount = $event->participants()->count();

        $event->delete();

        $kept = [];

        if ($participantCount > 0) {
            $kept[] = "{$participantCount} participant(s)";
        }

        if ($certificateCount > 0) {
            $kept[] = "{$certificateCount} certificate(s)";
        }

        $message = 'Event moved to Recycle Bin. You can restore it from Settings → Global Config → Recycle Bin.';

        if ($kept) {
            $message .= ' ' . implode(' and ', $kept) . ' were kept.';
        }

        return redirect()->route('event.management')->with('success', $message);
    }
    
    /**
     * Generate QR Code for event registration
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function generateQrCode($id)
    {
        // Find the event from database
        $event = Event::find($id);
        
        if (!$event) {
            return redirect()->route('event.management')
                ->with('error', 'Event not found.');
        }
        
        // The QR code embeds the registration token, so it must be restricted to
        // the event owner in the same way as show() and edit().
        if (!$this->canManage($event)) {
            return redirect()->route('event.management')
                ->with('error', 'You do not have permission to view this event.');
        }
        
        // Generate registration link
        $registrationLink = route('event.register', ['token' => $event->registration_link]);
        
        // Generate QR code using BaconQrCode
        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($registrationLink);
        
        // Generate PDF with TCPDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('SIJIL');
        $pdf->SetAuthor('SIJIL Event Management');
        $pdf->SetTitle('Event Registration QR Code');
        $pdf->SetSubject('Event Registration');
        
        // Remove header and footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 12);
        
        // Card size (smaller than A4, centered)
        $cardWidth = 150; // mm
        $cardHeight = 210; // mm
        $pageWidth = $pdf->getPageWidth();
        $pageHeight = $pdf->getPageHeight();
        $x = ($pageWidth - $cardWidth) / 2;
        $y = ($pageHeight - $cardHeight) / 2;
        $qrWidth = 60; // mm
        $qrX = $x + ($cardWidth - $qrWidth) / 2;
        $qrY = $y + 120; // position QR code below info section

        // 1. Render card (without QR code)
        $html = '
        <table style="width: ' . $cardWidth . 'mm; height: ' . $cardHeight . 'mm; margin: 0 auto; border: 2px solid #111827; background: #fff; font-family: Arial, Helvetica, sans-serif;" cellpadding="0" cellspacing="0">
            <tr><td colspan="3" style="text-align: center; padding-top: 18mm; padding-bottom: 2mm;">
                <span style="font-size: 26px; font-weight: bold; color: #111827;">Event Registration QR Code</span><br>
                <span style="font-size: 15px; color: #64748b;">Scan this QR code to register for the event</span>
            </td></tr>
            <tr><td colspan="3" style="text-align: center; padding-bottom: 2mm;">
                <span style="font-size: 22px; font-weight: bold; color: #111827;">' . htmlspecialchars($event->name) . '</span><br>
                <span style="font-size: 13px; color: #6b7280;">Organizer : ' . htmlspecialchars($event->organizer) . '</span>
            </td></tr>
            <tr><td colspan="3" style="padding-bottom: 4mm;">
                <table cellpadding="0" cellspacing="0" style="width: 100%; background: #e0eaff;">
                    <tr>
                        <td style="width: 33.3%; padding: 8px 4px; vertical-align: top; text-align: left;">
                            <span style="font-size: 13px; color: #6366f1; font-weight: bold;">Date:</span><br>
                            <span style="font-size: 16px;">ðŸ“…</span> <span style="font-size: 12px; color: #22223b;">' . $event->start_date->format('l, d F Y') .
                            ($event->start_date != $event->end_date ? '<br>to<br>' . $event->end_date->format('l, d F Y') : '') . '</span>
                        </td>
                        <td style="width: 33.3%; padding: 8px 4px; vertical-align: top; text-align: left;">
                            <span style="font-size: 13px; color: #6366f1; font-weight: bold;">Time:</span><br>
                            <span style="font-size: 16px;">â°</span> <span style="font-size: 12px; color: #22223b;">' . ($event->start_time ? substr($event->start_time,0,5) : '-') . ' - ' . ($event->end_time ? substr($event->end_time,0,5) : '-') . '</span>
                        </td>
                        <td style="width: 33.3%; padding: 8px 4px; vertical-align: top; text-align: left;">
                            <span style="font-size: 13px; color: #6366f1; font-weight: bold;">Location:</span><br>
                            <span style="font-size: 16px;">ðŸ“</span> <span style="font-size: 12px; color: #22223b;">' . htmlspecialchars($event->location) . '</span>
                        </td>
                    </tr>
                </table>
            </td></tr>
            <tr><td colspan="3" style="text-align: center; padding-top: 8mm; padding-bottom: 2mm;">
                <span style="font-size: 20px; font-weight: bold; color: #2563eb; letter-spacing: 1px;">SCAN HERE</span>
            </td></tr>
            <tr><td colspan="3" style="text-align: center; height: 70mm; vertical-align: middle;">
                __QR_CODE_PLACEHOLDER__
            </td></tr>
        </table>';

        // 2. Replace placeholder with empty div (so TCPDF keeps cell height)
        $htmlForWrite = str_replace('__QR_CODE_PLACEHOLDER__', '<div style="height: 60mm;"></div>', $html);
        $pdf->writeHTMLCell($cardWidth, $cardHeight, $x, $y, $htmlForWrite, 0, 1, 0, true, '', true);

        // 3. Render QR code centered in the card (in the reserved cell)
        $pdf->ImageSVG('@' . $qrCodeSvg, $qrX, $qrY, $qrWidth, $qrWidth, '', '', '', 0, false);
        
        // Close and output the PDF document
        return $pdf->Output('event-' . $id . '-qrcode.pdf', 'D');
    }
    
    /**
     * Download QR code as PNG image (only QR code, no extra info)
     */
    public function downloadQrCodeImage($id)
    {
        $event = Event::findOrFail($id);

        if (!$this->canManage($event)) {
            return redirect()->route('event.management')
                ->with('error', 'You do not have permission to view this event.');
        }

        $registrationLink = route('event.register', ['token' => $event->registration_link]);

        // Generate QR code SVG (BaconQrCode v2.x, set size via RendererStyle)
        $renderer = new \BaconQrCode\Renderer\Image\SvgImageBackEnd();
        $style = new \BaconQrCode\Renderer\RendererStyle\RendererStyle(800);
        $imageRenderer = new \BaconQrCode\Renderer\ImageRenderer($style, $renderer);
        $writer = new \BaconQrCode\Writer($imageRenderer);
        $qrSvg = $writer->writeString($registrationLink);

        return response($qrSvg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="event-' . $id . '-qrcode.svg"');
    }
    
    // Public event registration page
    public function register($token)
    {
        $event = Event::where('registration_link', $token)->first();
        
        if (!$event) {
            abort(404, 'Event not found');
        }
        
        if ($event->isRegistrationExpired()) {
            return view('events.registration-expired', compact('event'));
        }
        
        // The event page already advertises "spots remaining", so the limit is
        // enforced here rather than letting the form be submitted and rejected.
        if ($event->isFull()) {
            return view('events.registration-full', compact('event'));
        }
        
        return view('events.register', [
            'event' => $event
        ]);
    }

    // Handle registration submission
    public function registerSubmit(Request $request, $token)
    {
        // Get event first to check skip_identity_verification setting
        // This determines whether IC/Passport validation is required
        $event = Event::where('registration_link', $token)->first();
        if (!$event) {
            abort(404, 'Event not found');
        }
        
        // Conditional validation based on event's registration mode
        // Simplified mode: Only name and email are required
        // Verified mode: IC or Passport is also required
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'organization' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'race' => 'nullable|string|max:100',
            'job_title' => 'nullable|string|max:255',
            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'manual_state' => 'nullable|string|max:100',
            'manual_city' => 'nullable|string|max:100',
            'manual_postcode' => 'nullable|string|max:10',
        ];
        
        // Add IC/Passport validation only for verified registration
        if (!$event->skip_identity_verification) {
            $rules['identity_card'] = 'nullable|string|max:20';
            $rules['passport_no'] = 'nullable|string|max:20';
        }
        
        $request->validate($rules);
        
        // Server-side guard: prevent changing locked email/identity after verification
        $lockedEmail = $request->input('locked_email');
        $lockedIdType = $request->input('locked_id_type');
        $lockedIdentity = $request->input('locked_identity'); // ic digits or passport

        if ($lockedEmail && strtolower($lockedEmail) !== strtolower($request->email)) {
            return redirect()->back()->with('error', 'Email tidak boleh diubah selepas pengesahan.');
        }
        if ($lockedIdType === 'ic' && $lockedIdentity) {
            $normalizedIc = preg_replace('/\D+/', '', (string) $request->identity_card);
            if ($normalizedIc !== $lockedIdentity) {
                return redirect()->back()->with('error', 'IC tidak boleh diubah selepas pengesahan.');
            }
        }
        if ($lockedIdType === 'passport' && $lockedIdentity) {
            $normalizedPass = strtolower(preg_replace('/\s+/', '', (string) $request->passport_no));
            if ($normalizedPass !== strtolower($lockedIdentity)) {
                return redirect()->back()->with('error', 'Passport tidak boleh diubah selepas pengesahan.');
            }
        }

        // A banned person must not be able to come back under the same email or
        // identity number. Checked before anything is written.
        if (\App\Support\ParticipantBan::find($request->email, $request->identity_card, $request->passport_no)) {
            return redirect()->back()
                ->withInput($request->except(['identity_card', 'passport_no']))
                ->with('error', \App\Support\ParticipantBan::message());
        }

        if ($event->isRegistrationExpired()) {
            return redirect()->back()->with('error', 'Registration for this event has expired.');
        }
        // Re-check capacity at submit time: the event may have filled up while
        // this form was open.
        if ($event->isFull()) {
            return redirect()->back()
                ->with('error', 'This event has reached its maximum number of participants.');
        }
        // Check if already registered with same email
        $existingRegistration = Participant::where('event_id', $event->id)
            ->where('email', $request->email)
            ->exists();
        if ($existingRegistration) {
            return redirect()->back()->with('error', 'You are already registered for this event with this email address.');
        }
        // Format phone number with country code
        $phone = $request->phone;
        if ($phone) {
            $phone = ltrim($phone, '+');
            if (substr($phone, 0, 1) === '0') {
                $phone = substr($phone, 1);
            }
            if (!preg_match('/^60/', $phone)) {
                $phone = '60' . $phone;
            }
        }
        // Handle address fields
        $state = $request->state;
        $city = $request->city;
        $postcode = $request->postcode;
        if ($state === 'others') {
            $state = $request->manual_state;
            $city = $request->manual_city;
            $postcode = $request->manual_postcode;
        }
        $participant = new Participant([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $phone,
            'organization' => $request->organization,
            'notes' => $request->notes,
            'identity_card' => $request->identity_card,
            'passport_no' => $request->passport_no,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'race' => $request->race,
            'job_title' => $request->job_title,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'state' => $state,
            'city' => $city,
            'postcode' => $postcode,
            'country' => $request->country,
            'status' => 'active',
            'registration_date' => now(),
            'event_id' => $event->id,
            'registration_type' => $event->skip_identity_verification ? 'simplified' : 'verified',
        ]);
        $participant->save();
        
        // Create notification for event organizer
        if ($event->user_id) {
            \App\Models\Notification::create([
                'user_id' => $event->user_id,
                'type' => 'event_registration',
                'title' => 'New Event Registration',
                'message' => $request->name . ' has registered for ' . $event->name,
                'icon' => 'person_add',
                'url' => route('participants') . '?event_id=' . $event->id,
                'data' => [
                    'event_id' => $event->id,
                    'event_name' => $event->name,
                    'participant_id' => $participant->id,
                    'participant_name' => $participant->name,
                    'participant_email' => $participant->email
                ]
            ]);
        }
        
        // Get global configuration for notifications
        $globalConfig = \App\Models\GlobalConfig::getConfig();
        
        // Send email to participant if enabled
        // Use different email template based on event's registration mode
        if ($globalConfig && $globalConfig->email_event_registration) {
            try {
                $emailService = new \App\Services\EmailService();
                
                // Simplified registration: no PWA portal mention
                // Verified registration: includes PWA portal access info
                if ($event->skip_identity_verification) {
                    $mailable = new \App\Mail\EventRegistrationSimplified($event, $participant);
                } else {
                    $mailable = new \App\Mail\EventRegistrationConfirmation($event, $participant);
                }
                
                $emailService->sendEmail($event->user_id, $mailable, $participant->email);
            } catch (\Exception $e) {
                Log::error('Failed to send participant email: ' . $e->getMessage());
            }
        }
        
        // Send email to organizer if enabled
        if ($globalConfig && $globalConfig->admin_new_registrations && $event->user) {
            try {
                $emailService = new \App\Services\EmailService();
                $mailable = new \App\Mail\NewEventRegistrationNotification($event, $participant);
                $emailService->sendEmail($event->user_id, $mailable, $event->user->email);
            } catch (\Exception $e) {
                Log::error('Failed to send organizer email: ' . $e->getMessage());
            }
        }
        
        // Send SMS to participant if enabled
        if ($globalConfig && $globalConfig->sms_event_registration && $participant->phone) {
            try {
                $infobipService = new \App\Services\InfobipService();
                $message = "Thank you for registering for {$event->name}. Date: {$event->start_date->format('d/m/Y')} at {$event->location}. We look forward to seeing you!";
                $infobipService->sendSms($participant->phone, $message, $event->user_id);
            } catch (\Exception $e) {
                Log::error('Failed to send SMS: ' . $e->getMessage());
            }
        }
        
        // Send Telegram notification if enabled
        if ($globalConfig && $globalConfig->telegram_event_registration) {
            try {
                $telegramService = new \App\Services\TelegramService();
                if ($telegramService->isEnabled()) {
                    $telegramService->sendEventRegistrationNotification($participant, $event);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send Telegram notification: ' . $e->getMessage());
            }
        }

        // Per-event automation. Each step is isolated so a failure in one does
        // not cost the participant their registration, which is already saved.
        \App\Services\EventAutomation::runAfterRegistration($event, $participant);

        return redirect()->route('event.register.thankyou', $event->registration_link);
    }

    // Thank you page after successful public registration
    public function registerThankYou($token)
    {
        $event = Event::where('registration_link', $token)->first();
        if (!$event) {
            abort(404, 'Event not found');
        }
        return view('events.register-thankyou', [
            'event' => $event
        ]);
    }
    
} 