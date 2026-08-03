<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
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
        $perPage = \App\Support\SystemSettings::perPage($request, 10);
        
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
        return view('campaign.create', [
            'events' => $this->availableEvents(),
        ]);
    }

    /**
     * The rules for saving a campaign, whether new or existing.
     *
     * Create and update carried two copies of these, which is how they drifted
     * apart: only one of them rejected an empty audience.
     */
    private function validateCampaign(Request $request): array
    {
        $rules = [
            'campaign_name' => 'required|string|max:255',
            'campaign_description' => 'nullable|string|max:2000',
            'campaign_type' => ['required', Rule::in(Campaign::types())],
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'audience_type' => ['required', Rule::in(Campaign::audiences())],

            // Scoped, not a bare exists check. An organizer could otherwise post
            // any event id and mail another account's participants.
            'event_id' => [
                'nullable',
                'required_if:audience_type,' . Campaign::AUDIENCE_EVENT,
                Rule::exists('events', 'id')->where(function ($query) {
                    if (! Auth::user()->hasRole('Administrator')) {
                        $query->where('user_id', Auth::id());
                    }
                }),
            ],

            'filter_age' => ['nullable', Rule::in(['18-24', '25-34', '35-44', '45+'])],
            'filter_gender' => ['nullable', Rule::in(['male', 'female'])],
            'filter_attendance' => ['nullable', Rule::in(['attended', 'not_attended'])],

            'schedule_type' => ['required', Rule::in(Campaign::scheduleTypes())],
            'scheduled_date' => 'required_if:schedule_type,' . Campaign::SCHEDULE_LATER . '|nullable|date',
            'scheduled_time' => 'required_if:schedule_type,' . Campaign::SCHEDULE_LATER . '|nullable|date_format:H:i',
        ];

        if ($request->input('campaign_type') === Campaign::TYPE_EMAIL) {
            $rules['email_subject'] = 'required|string|max:255';
            $rules['email_content'] = 'required|string';
        }

        if ($request->input('campaign_type') === Campaign::TYPE_SMS) {
            $rules['sms_message'] = 'required|string|max:160';

            // An address list carries no phone numbers, so this pairing can only ever
            // send to nobody. It used to be accepted, run, and report success with
            // zero delivered.
            $rules['audience_type'] = [
                'required',
                Rule::in(array_values(array_diff(Campaign::audiences(), [Campaign::AUDIENCE_EMAILS]))),
            ];
        }

        if ($request->input('audience_type') === Campaign::AUDIENCE_EMAILS) {
            // Invalid addresses used to be dropped without a word, so a typo in the
            // only address produced a campaign that reported "no recipients found".
            $rules['custom_emails'] = ['required', 'string', function ($attribute, $value, $fail) {
                $addresses = self::parseEmails($value);

                if (! $addresses) {
                    $fail('Enter at least one email address.');

                    return;
                }

                $invalid = array_filter($addresses, fn ($email) => ! filter_var($email, FILTER_VALIDATE_EMAIL));

                if ($invalid) {
                    $fail('Not a valid email address: ' . implode(', ', $invalid));
                }
            }];
        }

        return $request->validate($rules, [
            'event_id.exists' => 'Choose one of your own events.',
            'event_id.required_if' => 'Choose the event to send to.',
            'audience_type.in' => 'An address list has no phone numbers, so it cannot be used for SMS. '
                . 'Pick an event or a filter instead.',
            'end_date.after_or_equal' => 'The end date cannot be before the start date.',
            'sms_message.max' => 'An SMS is limited to 160 characters.',
            'scheduled_date.required_if' => 'Choose the date to send on.',
            'scheduled_time.required_if' => 'Choose the time to send at.',
        ]);
    }

    /**
     * Split a comma, semicolon or newline separated list into addresses.
     */
    private static function parseEmails(?string $value): array
    {
        return array_values(array_filter(
            array_map('trim', preg_split('/[,;\r\n]+/', (string) $value)),
            fn ($email) => $email !== ''
        ));
    }

    /**
     * The column values for a campaign, minus user_id and status.
     */
    private function payload(Request $request): array
    {
        $type = $request->input('campaign_type');
        $audience = $request->input('audience_type');

        // include_unsubscribe and include_shortlink used to be stored here. Neither
        // was ever read when sending, so the form was offering an unsubscribe link
        // and a shortlink that never appeared. They are gone rather than left as a
        // promise the sender does not keep.
        $content = $type === Campaign::TYPE_EMAIL
            ? [
                'subject' => $request->input('email_subject'),
                'body' => $request->input('email_content'),
            ]
            : [
                'message' => $request->input('sms_message'),
            ];

        $criteria = match ($audience) {
            Campaign::AUDIENCE_FILTER => [
                'age' => $request->input('filter_age') ?: null,
                'gender' => $request->input('filter_gender') ?: null,
                'attendance' => $request->input('filter_attendance') ?: null,
            ],
            Campaign::AUDIENCE_EMAILS => [
                'custom_emails' => self::parseEmails($request->input('custom_emails')),
            ],
            default => null,
        };

        $scheduledAt = null;

        if ($request->input('schedule_type') === Campaign::SCHEDULE_LATER) {
            $scheduledAt = Carbon::parse(
                $request->input('scheduled_date') . ' ' . $request->input('scheduled_time')
            );
        }

        return [
            'name' => $request->input('campaign_name'),
            'description' => $request->input('campaign_description'),
            'campaign_type' => $type,
            'audience_type' => $audience,
            'event_id' => $audience === Campaign::AUDIENCE_EVENT ? $request->input('event_id') : null,
            'filter_criteria' => $criteria,
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date') ?: null,
            'content' => $content,
            'schedule_type' => $request->input('schedule_type'),
            'scheduled_at' => $scheduledAt,
        ];
    }

    /**
     * Resolve the route parameter and check the caller owns the campaign.
     *
     * show, edit, update and destroy each carried their own copy of this.
     */
    private function findCampaign($campaign): Campaign
    {
        $model = is_numeric($campaign)
            ? Campaign::findOrFail($campaign)
            // Exact match, not LIKE: a name containing % or _ would otherwise be
            // treated as a wildcard and resolve to somebody else's campaign.
            : Campaign::where('name', str_replace('-', ' ', $campaign))->firstOrFail();

        if (! Auth::user()->hasRole('Administrator') && (int) $model->user_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return $model;
    }

    /**
     * The status a save should leave the campaign in.
     */
    private function statusAfterSave(Request $request): string
    {
        if (! $request->has('save_send')) {
            return Campaign::STATUS_DRAFT;
        }

        return $request->input('schedule_type') === Campaign::SCHEDULE_LATER
            ? Campaign::STATUS_SCHEDULED
            : Campaign::STATUS_RUNNING;
    }

    /**
     * Where to send the user after a save, with a message that says what happened.
     */
    private function afterSave(Campaign $campaign, string $status, string $verb)
    {
        if ($status === Campaign::STATUS_RUNNING) {
            $this->processCampaign($campaign);

            return redirect()->route('campaign.show', $campaign->id)
                ->with('success', "Campaign {$verb} and sent. The delivery figures below are from this run.");
        }

        if ($status === Campaign::STATUS_SCHEDULED) {
            return redirect()->route('campaign.show', $campaign->id)
                ->with('success', "Campaign {$verb} and scheduled for "
                    . $campaign->scheduled_at->format('j M Y, H:i') . '.');
        }

        return redirect()->route('campaign.show', $campaign->id)
            ->with('success', "Campaign {$verb} as a draft. Use Send now when you are ready.");
    }

    /**
     * Store a newly created campaign in storage.
     */
    public function store(Request $request)
    {
        $this->validateCampaign($request);

        $status = $this->statusAfterSave($request);

        $campaign = Campaign::create($this->payload($request) + [
            'user_id' => Auth::id(),
            'status' => $status,
        ]);

        return $this->afterSave($campaign, $status, 'created');
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
        $campaign = $this->findCampaign($campaign);

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
        $campaign = $this->findCampaign($campaign);

        // The form reads the address list straight out of filter_criteria. The old
        // code assigned it to $campaign->custom_emails, a phantom attribute with no
        // column behind it, which would have thrown on any later save().
        return view('campaign.edit', [
            'campaign' => $campaign,
            'events' => $this->availableEvents(),
            'id' => $campaign->id, // For backward compatibility with the view
        ]);
    }

    /**
     * Events this account may target.
     */
    private function availableEvents()
    {
        return Event::when(! Auth::user()->hasRole('Administrator'), function ($query) {
            return $query->where('user_id', Auth::id());
        })->orderBy('name')->get();
    }

    /**
     * Update the specified campaign in storage.
     */
    public function update(Request $request, $campaign)
    {
        $campaign = $this->findCampaign($campaign);

        $this->validateCampaign($request);

        $attributes = $this->payload($request);

        // Saving and sending are separate decisions. update() never touched status
        // before, and nothing in the interface linked to the process route, so a
        // draft could not be sent at all once it had been saved.
        $status = $campaign->status;

        if ($request->has('save_send') && $campaign->isSendable()) {
            $status = $this->statusAfterSave($request);
            $attributes['status'] = $status;
        }

        $campaign->update($attributes);

        // A campaign that has already gone out is not sent again by saving it.
        if (! $request->has('save_send')) {
            return redirect()->route('campaign.show', $campaign->id)
                ->with('success', 'Campaign updated.');
        }

        return $this->afterSave($campaign->refresh(), $status, 'updated');
    }

    /**
     * Remove the specified campaign from storage.
     */
    public function destroy($campaign)
    {
        $campaign = $this->findCampaign($campaign);

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
        $campaign = $this->findCampaign($id);

        // Guards against a second click resending to everyone. The processor marks a
        // campaign completed when it finishes, so this is the honest check.
        if (! $campaign->isSendable()) {
            return redirect()->route('campaign.show', $campaign->id)
                ->with('error', 'This campaign has already been sent.');
        }

        $campaign->update(['status' => Campaign::STATUS_RUNNING]);

        $this->processCampaign($campaign);

        return redirect()->route('campaign.show', $campaign->id)
            ->with('success', 'Campaign sent. The delivery figures below are from this run.');
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