<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use TCPDF;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class EventManagementController extends Controller
{
    /**
     * Display the event management page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Start with base query
        $query = Event::with('participants');

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
        return view('events.create');
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
            'max_participants' => 'required|integer|min:1',
            'status' => 'required|in:active,pending,completed',
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
        
        // Save first to get ID
        $event->save();
        
        // Generate the registration link and save again
        $event->generateRegistrationLink();
        $event->save();
        
        return redirect()->route('event.management')
            ->with('success', 'Event created successfully!');
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
        if (!auth()->user()->hasRole('Administrator') && $event->user_id != auth()->id()) {
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
        if (!auth()->user()->hasRole('Administrator') && $event->user_id != auth()->id()) {
            return redirect()->route('event.management')
                ->with('error', 'You do not have permission to edit this event.');
        }
        
        // Add formatted date and time fields for the form
        $event->start_date_formatted = $event->start_date ? $event->start_date->format('Y-m-d') : null;
        $event->end_date_formatted = $event->end_date ? $event->end_date->format('Y-m-d') : null;
        $event->start_time_formatted = $event->start_time;
        $event->end_time_formatted = $event->end_time;
        
        return view('events.edit', [
            'event' => $event
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
            'max_participants' => 'required|integer|min:1',
            'status' => 'required|in:active,pending,completed',
        ]);
        
        // Find the event
        $event = Event::find($id);
        
        // If event not found, redirect with error
        if (!$event) {
            return redirect()->route('event.management')
                ->with('error', 'Event not found.');
        }
        
        // Check if user has permission to update this event
        if (!auth()->user()->hasRole('Administrator') && $event->user_id != auth()->id()) {
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
        
        // Save the event
        $event->save();
        
        return redirect()->route('event.management')
            ->with('success', 'Event updated successfully!');
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
        if (!auth()->user()->hasRole('Administrator') && $event->user_id != auth()->id()) {
            return redirect()->route('event.management')
                ->with('error', 'You do not have permission to delete this event.');
        }
        
        // Delete the event
        $event->delete();
        
        return redirect()->route('event.management')
            ->with('success', 'Event deleted successfully!');
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
        
        // Create temporary file for SVG
        $tempSvgFile = tempnam(sys_get_temp_dir(), 'qrcode_') . '.svg';
        file_put_contents($tempSvgFile, $qrCodeSvg);
        
        // Add content
        $html = '
        <h1 style="text-align:center; color:#2563EB;">Event Registration QR Code</h1>
        <p style="text-align:center;">Scan this QR code to register for the event</p>
        
        <div style="margin-top:20px; margin-bottom:20px;">
            <h2 style="color:#2563EB;">' . $event->name . '</h2>
            <p><strong>Organizer:</strong> ' . $event->organizer . '</p>
            <p><strong>Date:</strong> ' . $event->start_date->format('d M Y') . ' to ' . $event->end_date->format('d M Y') . '</p>
            <p><strong>Location:</strong> ' . $event->location . '</p>
        </div>
        
        <div style="text-align:center; margin-top:20px; margin-bottom:20px;">';
        
        // Add QR code as image
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Add SVG QR code to PDF
        $pdf->ImageSVG($tempSvgFile, 80, 120, 50, 50);
        
        // Continue with rest of HTML
        $html = '
        </div>
        
        <div style="text-align:center; margin-top:60px; font-size:10px; color:#666;">
            <p>Registration Link: ' . $registrationLink . '</p>
            <p>Generated on: ' . date('d M Y - H:i:s') . '</p>
            <p>This QR code will expire when the event begins.</p>
        </div>
        ';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Remove temporary file
        unlink($tempSvgFile);
        
        // Close and output the PDF document
        return $pdf->Output('event-' . $id . '-qrcode.pdf', 'D');
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
        
        return view('events.register', [
            'event' => $event
        ]);
    }

    // Handle registration submission
    public function registerSubmit(Request $request, $token)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'organization' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'identity_card' => 'nullable|string|max:20',
            'passport_no' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
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
        ]);
        
        $event = Event::where('registration_link', $token)->first();
        if (!$event) {
            abort(404, 'Event not found');
        }
        if ($event->isRegistrationExpired()) {
            return redirect()->back()->with('error', 'Registration for this event has expired.');
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
        ]);
        $participant->save();
        return redirect()->back()->with('success', 'Thank you for registering! You will receive a confirmation email shortly.');
    }
    
    /**
     * Get sample events for demo purposes.
     *
     * @return array
     */
    private function getSampleEvents()
    {
        $events = [
            [
                'id' => 1,
                'name' => 'Annual Leadership Conference 2025',
                'organizer' => 'Human Resource Division',
                'description' => 'A three-day leadership development conference focusing on emerging leadership skills, team building, and organizational management strategies.',
                'start_date' => '21 Jul 2025 - 09:00:00',
                'end_date' => '23 Jul 2025 - 17:00:00',
                'start_date_formatted' => '2025-07-21',
                'start_time_formatted' => '09:00',
                'end_date_formatted' => '2025-07-23',
                'end_time_formatted' => '17:00',
                'location' => 'Kuala Lumpur Convention Center',
                'address' => 'Kuala Lumpur City Centre, 50088 Kuala Lumpur, Malaysia',
                'max_participants' => 200,
                'participants' => 150,
                'status' => 'active',
                'created_at' => '21 Jun 2025 - 10:15:00',
                'created_by' => 'Administrator',
                'user_id' => 1, // Admin user ID
                'contact_person' => 'Sarah Johnson',
                'contact_email' => 'sarah.johnson@example.com',
                'contact_phone' => '+60123456789',
                'registration_link' => base64_encode('event_1_' . time()),
            ],
            [
                'id' => 2,
                'name' => 'Digital Transformation Workshop',
                'organizer' => 'IT Department',
                'description' => 'Hands-on workshop exploring digital tools, automation, and technology adoption strategies for organizational efficiency.',
                'start_date' => '15 Aug 2025 - 10:00:00',
                'end_date' => '16 Aug 2025 - 16:30:00',
                'start_date_formatted' => '2025-08-15',
                'start_time_formatted' => '10:00',
                'end_date_formatted' => '2025-08-16',
                'end_time_formatted' => '16:30',
                'location' => 'RISDA Training Center',
                'address' => '123 Jalan RISDA, 50000 Kuala Lumpur, Malaysia',
                'max_participants' => 100,
                'participants' => 75,
                'status' => 'active',
                'created_at' => '25 Jun 2025 - 14:30:00',
                'created_by' => 'John Tech',
                'user_id' => 2, // Organizer user ID
                'contact_person' => 'Michael Lee',
                'contact_email' => 'michael.lee@example.com',
                'contact_phone' => '+60129876543',
                'registration_link' => base64_encode('event_2_' . time()),
            ],
            [
                'id' => 3,
                'name' => 'Sustainable Agriculture Seminar',
                'organizer' => 'Agriculture Division',
                'description' => 'Seminar on modern sustainable farming practices, renewable resources, and eco-friendly agricultural methods.',
                'start_date' => '10 Sep 2025 - 08:30:00',
                'end_date' => '10 Sep 2025 - 17:00:00',
                'start_date_formatted' => '2025-09-10',
                'start_time_formatted' => '08:30',
                'end_date_formatted' => '2025-09-10',
                'end_time_formatted' => '17:00',
                'location' => 'Putrajaya International Convention Centre',
                'address' => 'Dataran Gemilang, Presint 5, 62000 Putrajaya, Malaysia',
                'max_participants' => 250,
                'participants' => 200,
                'status' => 'pending',
                'created_at' => '30 Jun 2025 - 09:45:00',
                'created_by' => 'Ahmad Kassim',
                'user_id' => 1, // Admin user ID
                'contact_person' => 'David Wong',
                'contact_email' => 'david.wong@example.com',
                'contact_phone' => '+60132345678',
                'registration_link' => base64_encode('event_3_' . time()),
            ],
            [
                'id' => 4,
                'name' => 'Financial Management Course',
                'organizer' => 'Finance Department',
                'description' => 'Comprehensive course covering budgeting, financial planning, investment strategies, and financial risk management.',
                'start_date' => '05 Oct 2025 - 09:00:00',
                'end_date' => '07 Oct 2025 - 16:00:00',
                'start_date_formatted' => '2025-10-05',
                'start_time_formatted' => '09:00',
                'end_date_formatted' => '2025-10-07',
                'end_time_formatted' => '16:00',
                'location' => 'RISDA Headquarters',
                'address' => '123 Jalan RISDA, 50000 Kuala Lumpur, Malaysia',
                'max_participants' => 50,
                'participants' => 50,
                'status' => 'completed',
                'created_at' => '15 Jul 2025 - 11:20:00',
                'created_by' => 'Lisa Tan',
                'user_id' => 2, // Organizer user ID
                'contact_person' => 'Robert Chen',
                'contact_email' => 'robert.chen@example.com',
                'contact_phone' => '+60145678901',
                'registration_link' => base64_encode('event_4_' . time()),
            ]
        ];
        
        // If user is Organizer, filter events to show only their own
        if (auth()->user()->hasRole('Organizer')) {
            return array_filter($events, function($event) {
                return $event['user_id'] == auth()->id();
            });
        }
        
        // For Administrator, return all events
        return $events;
    }
} 