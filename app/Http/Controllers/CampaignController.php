<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CampaignController extends Controller
{
    /**
     * Display a listing of the campaigns.
     */
    public function index(Request $request)
    {
        $query = Campaign::query();
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('campaign_type', 'LIKE', "%{$search}%");
            });
        }
        
        // Apply filters
        if ($request->filled('campaign_type')) {
            $query->ofType($request->campaign_type);
        }
        
        if ($request->filled('status')) {
            $query->withStatus($request->status);
        }
        
        // For non-admin users, only show their own campaigns
        if (!Auth::user()->hasRole('Administrator')) {
            $query->forCurrentUser();
        }
        
        // Get per_page parameter with default 10
        $perPage = $request->get('per_page', 10);
        
        $campaigns = $query->with(['user', 'event'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
            
        // Get statistics for summary cards
        $totalCampaigns = Campaign::when(!Auth::user()->hasRole('Administrator'), function($q) {
            return $q->forCurrentUser();
        })->count();
        
        $activeCampaigns = Campaign::when(!Auth::user()->hasRole('Administrator'), function($q) {
            return $q->forCurrentUser();
        })->whereIn('status', ['scheduled', 'running'])->count();
        
        $averageOpenRate = Campaign::when(!Auth::user()->hasRole('Administrator'), function($q) {
            return $q->forCurrentUser();
        })->where('delivered_count', '>', 0)
            ->selectRaw('SUM(opened_count) as total_opened, SUM(delivered_count) as total_delivered')
            ->first();
            
        $avgOpenRate = 0;
        if ($averageOpenRate && $averageOpenRate->total_delivered > 0) {
            $avgOpenRate = round(($averageOpenRate->total_opened / $averageOpenRate->total_delivered) * 100);
        }
        
        return view('campaign.index', [
            'campaigns' => $campaigns,
            'totalCampaigns' => $totalCampaigns,
            'activeCampaigns' => $activeCampaigns,
            'averageOpenRate' => $avgOpenRate,
        ]);
    }

    /**
     * Show the form for creating a new campaign.
     */
    public function create()
    {
        $events = Event::when(!Auth::user()->hasRole('Administrator'), function($q) {
            return $q->where('user_id', Auth::id());
        })->orderBy('name')->get();
        
        return view('campaign.create', [
            'events' => $events,
        ]);
    }

    /**
     * Store a newly created campaign in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campaign_name' => 'required|string|max:255',
            'campaign_description' => 'nullable|string',
            'campaign_type' => 'required|in:email,sms,whatsapp',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'audience_type' => 'required|in:all_participants,specific_event,custom_filter,custom_emails',
            'event_id' => 'required_if:audience_type,specific_event|nullable|exists:events,id',
            'custom_emails' => 'required_if:audience_type,custom_emails|nullable|string',
            'schedule_type' => 'required|in:now,scheduled',
            'scheduled_date' => 'required_if:schedule_type,scheduled|nullable|date',
            'scheduled_time' => 'required_if:schedule_type,scheduled|nullable',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Prepare content based on campaign type
        $content = [];
        if ($request->campaign_type == 'email') {
            $validator = Validator::make($request->all(), [
                'email_subject' => 'required|string|max:255',
                'email_content' => 'required|string',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            $content = [
                'subject' => $request->email_subject,
                'body' => $request->email_content,
                'include_unsubscribe' => $request->has('include_unsubscribe'),
            ];
        } elseif ($request->campaign_type == 'sms') {
            $validator = Validator::make($request->all(), [
                'sms_message' => 'required|string|max:160',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            $content = [
                'message' => $request->sms_message,
                'include_shortlink' => $request->has('include_shortlink'),
            ];
        }
        
        // Prepare filter criteria for custom filter or custom emails
        $filterCriteria = null;
        if ($request->audience_type == 'custom_filter') {
            $filterCriteria = [
                'age' => $request->filter_age,
                'gender' => $request->filter_gender,
                'attendance' => $request->filter_attendance,
            ];
        } elseif ($request->audience_type == 'custom_emails') {
            // Process the custom emails from textarea
            $emails = array_map('trim', explode(',', $request->custom_emails));
            // Filter out empty values and validate each email
            $validEmails = array_filter($emails, function($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL);
            });
            
            $filterCriteria = [
                'custom_emails' => $validEmails
            ];
        }
        
        // Prepare scheduled_at datetime
        $scheduledAt = null;
        if ($request->schedule_type == 'scheduled' && $request->scheduled_date && $request->scheduled_time) {
            $scheduledAt = Carbon::parse($request->scheduled_date . ' ' . $request->scheduled_time);
        }
        
        // Determine initial status
        $status = 'draft';
        if ($request->has('save_send')) {
            $status = ($request->schedule_type == 'now') ? 'running' : 'scheduled';
        }
        
        // Create the campaign
        $campaign = Campaign::create([
            'user_id' => Auth::id(),
            'name' => $request->campaign_name,
            'description' => $request->campaign_description,
            'campaign_type' => $request->campaign_type,
            'audience_type' => $request->audience_type,
            'event_id' => $request->audience_type == 'specific_event' ? $request->event_id : null,
            'filter_criteria' => $filterCriteria,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $status,
            'content' => $content,
            'schedule_type' => $request->schedule_type,
            'scheduled_at' => $scheduledAt,
        ]);
        
        // Process campaign immediately if it's set to running
        if ($status === 'running' && $request->schedule_type === 'now') {
            $this->processCampaign($campaign);
        }
        
        return redirect()->route('campaign.index')
            ->with('success', 'Campaign created successfully.');
    }
    
    /**
     * Process a campaign for immediate delivery.
     *
     * @param  \App\Models\Campaign  $campaign
     * @return void
     */
    protected function processCampaign(Campaign $campaign)
    {
        try {
            // Call the Artisan command to process the campaign
            \Illuminate\Support\Facades\Artisan::call('campaigns:process', [
                'campaign_id' => $campaign->id
            ]);
            
            // Get the output for logging
            $output = \Illuminate\Support\Facades\Artisan::output();
            // Campaign processing output
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error processing campaign:', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified campaign.
     */
    public function show($campaign)
    {
        // Check if $campaign is numeric (ID) or string (slug)
        if (is_numeric($campaign)) {
            $campaign = Campaign::findOrFail($campaign);
        } else {
            $campaign = Campaign::where('name', 'like', str_replace('-', ' ', $campaign))->firstOrFail();
        }
        
        // Check if user has permission to view this campaign
        if (!Auth::user()->hasRole('Administrator') && $campaign->user_id != Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('campaign.show', [
            'campaign' => $campaign,
            'id' => $campaign->id, // For backward compatibility with the view
        ]);
    }

    /**
     * Show the form for editing the specified campaign.
     */
    public function edit($campaign)
    {
        // Check if $campaign is numeric (ID) or string (slug)
        if (is_numeric($campaign)) {
            $campaign = Campaign::findOrFail($campaign);
        } else {
            $campaign = Campaign::where('name', 'like', str_replace('-', ' ', $campaign))->firstOrFail();
        }
        
        // Check if user has permission to edit this campaign
        if (!Auth::user()->hasRole('Administrator') && $campaign->user_id != Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $events = Event::when(!Auth::user()->hasRole('Administrator'), function($q) {
            return $q->where('user_id', Auth::id());
        })->orderBy('name')->get();
        
        // Prepare custom emails for display if applicable
        if ($campaign->audience_type == 'custom_emails' && isset($campaign->filter_criteria['custom_emails'])) {
            $campaign->custom_emails = implode(', ', $campaign->filter_criteria['custom_emails']);
        }
        
        return view('campaign.edit', [
            'campaign' => $campaign,
            'events' => $events,
            'id' => $campaign->id, // For backward compatibility with the view
        ]);
    }

    /**
     * Update the specified campaign in storage.
     */
    public function update(Request $request, $campaign)
    {
        // Check if $campaign is numeric (ID) or string (slug)
        if (is_numeric($campaign)) {
            $campaign = Campaign::findOrFail($campaign);
        } else {
            $campaign = Campaign::where('name', 'like', str_replace('-', ' ', $campaign))->firstOrFail();
        }
        
        // Check if user has permission to update this campaign
        if (!Auth::user()->hasRole('Administrator') && $campaign->user_id != Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $validator = Validator::make($request->all(), [
            'campaign_name' => 'required|string|max:255',
            'campaign_description' => 'nullable|string',
            'campaign_type' => 'required|in:email,sms,whatsapp',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'audience_type' => 'required|in:all_participants,specific_event,custom_filter,custom_emails',
            'event_id' => 'required_if:audience_type,specific_event|nullable|exists:events,id',
            'custom_emails' => 'required_if:audience_type,custom_emails|nullable|string',
            'schedule_type' => 'required|in:now,scheduled',
            'scheduled_date' => 'required_if:schedule_type,scheduled|nullable|date',
            'scheduled_time' => 'required_if:schedule_type,scheduled|nullable',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Prepare content based on campaign type
        $content = $campaign->content;
        if ($request->campaign_type == 'email') {
            $validator = Validator::make($request->all(), [
                'email_subject' => 'required|string|max:255',
                'email_content' => 'required|string',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            $content = [
                'subject' => $request->email_subject,
                'body' => $request->email_content,
                'include_unsubscribe' => $request->has('include_unsubscribe'),
            ];
        } elseif ($request->campaign_type == 'sms') {
            $validator = Validator::make($request->all(), [
                'sms_message' => 'required|string|max:160',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            $content = [
                'message' => $request->sms_message,
                'include_shortlink' => $request->has('include_shortlink'),
            ];
        }
        
        // Prepare filter criteria for custom filter
        $filterCriteria = $campaign->filter_criteria;
        if ($request->audience_type == 'custom_filter') {
            $filterCriteria = [
                'age' => $request->filter_age,
                'gender' => $request->filter_gender,
                'attendance' => $request->filter_attendance,
            ];
        } elseif ($request->audience_type == 'custom_emails') {
            // Process the custom emails from textarea
            $emails = array_map('trim', explode(',', $request->custom_emails));
            // Filter out empty values and validate each email
            $validEmails = array_filter($emails, function($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL);
            });
            
            $filterCriteria = [
                'custom_emails' => $validEmails
            ];
        }
        
        // Prepare scheduled_at datetime
        $scheduledAt = $campaign->scheduled_at;
        if ($request->schedule_type == 'scheduled' && $request->scheduled_date && $request->scheduled_time) {
            $scheduledAt = Carbon::parse($request->scheduled_date . ' ' . $request->scheduled_time);
        }
        
        // Update the campaign
        $campaign->update([
            'name' => $request->campaign_name,
            'description' => $request->campaign_description,
            'campaign_type' => $request->campaign_type,
            'audience_type' => $request->audience_type,
            'event_id' => $request->audience_type == 'specific_event' ? $request->event_id : null,
            'filter_criteria' => $filterCriteria,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'content' => $content,
            'schedule_type' => $request->schedule_type,
            'scheduled_at' => $scheduledAt,
        ]);
        
        return redirect()->route('campaign.index')
            ->with('success', 'Campaign updated successfully.');
    }

    /**
     * Remove the specified campaign from storage.
     */
    public function destroy($campaign)
    {
        // Check if $campaign is numeric (ID) or string (slug)
        if (is_numeric($campaign)) {
            $campaign = Campaign::findOrFail($campaign);
        } else {
            $campaign = Campaign::where('name', 'like', str_replace('-', ' ', $campaign))->firstOrFail();
        }
        
        // Check if user has permission to delete this campaign
        if (!Auth::user()->hasRole('Administrator') && $campaign->user_id != Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $campaign->delete();
        
        return redirect()->route('campaign.index')
            ->with('success', 'Campaign deleted successfully.');
    }

    /**
     * Process the campaign manually.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function process($id)
    {
        $campaign = Campaign::findOrFail($id);
        
        // Check if user has permission to process this campaign
        if (!Auth::user()->hasRole('Administrator') && $campaign->user_id != Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Process the campaign
        $this->processCampaign($campaign);
        
        return redirect()->route('campaign.show', ['campaign' => $id])
            ->with('success', 'Campaign is being processed. Please check back shortly for results.');
    }

    /**
     * Track email open event.
     *
     * @param  int  $campaign
     * @param  string  $recipient (base64 encoded recipient data)
     * @return \Illuminate\Http\Response
     */
    public function trackOpen($campaign, $recipient)
    {
        try {
            $campaignId = intval($campaign);
            $recipientData = json_decode(base64_decode($recipient), true);
            
            $campaign = Campaign::findOrFail($campaignId);
            
            // Increment opened count
            $campaign->increment('opened_count');

            // Also log into PWA email logs if the campaign id is a template id
            try {
                \App\Models\PwaEmailLog::create([
                    'template_id' => $campaignId,
                    'action' => 'open',
                    'quantity' => 1,
                    'meta' => ['recipient' => $recipientData['email'] ?? 'unknown']
                ]);
            } catch (\Throwable $e) {
                // ignore
            }
            
            // Log the open event for detailed analytics if needed
            // Email opened
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error tracking email open: ' . $e->getMessage());
        }
        
        // Return a 1x1 transparent pixel
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        return response($pixel)->header('Content-Type', 'image/gif');
    }

    /**
     * Track email click event.
     *
     * @param  int  $campaign
     * @param  string  $recipient (base64 encoded recipient data)
     * @param  string  $url (base64 encoded target URL)
     * @return \Illuminate\Http\Response
     */
    public function trackClick($campaign, $recipient, $url)
    {
        try {
            $campaignId = intval($campaign);
            $recipientData = json_decode(base64_decode($recipient), true);
            $targetUrl = base64_decode($url);
            
            $campaign = Campaign::findOrFail($campaignId);
            
            // Increment clicked count
            $campaign->increment('clicked_count');

            // Also log into PWA email logs if the campaign id is a template id
            try {
                \App\Models\PwaEmailLog::create([
                    'template_id' => $campaignId,
                    'action' => 'click',
                    'quantity' => 1,
                    'meta' => ['recipient' => $recipientData['email'] ?? 'unknown', 'url' => $targetUrl]
                ]);
            } catch (\Throwable $e) {
                // ignore
            }
            
            // Log the click event for detailed analytics if needed
            // Email link clicked
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error tracking email click: ' . $e->getMessage());
        }
        
        // Redirect to the target URL
        return redirect($targetUrl ?: url('/'));
    }
} 