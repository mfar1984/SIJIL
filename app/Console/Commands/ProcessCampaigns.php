<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Campaign;
use App\Models\Participant;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Helpers\EmailTracker;

class ProcessCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:process {campaign_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process running campaigns and send emails/sms';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $campaignId = $this->argument('campaign_id');
        
        // If specific campaign ID is provided, only process that campaign
        if ($campaignId) {
            $campaign = Campaign::find($campaignId);
            if (!$campaign) {
                $this->error("Campaign with ID {$campaignId} not found.");
                return 1;
            }
            $this->processCampaign($campaign);
            return 0;
        }
        
        // Everything that is due. 'scheduled' used to be excluded, so a campaign
        // queued for a future time sat in that state forever: the interface said it
        // was scheduled and nothing ever picked it up.
        $campaigns = Campaign::whereIn('status', [Campaign::STATUS_RUNNING, Campaign::STATUS_SCHEDULED])
            ->where(function ($query) {
                $query->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->get();

        $this->info("Found {$campaigns->count()} campaigns to process.");
        
        foreach ($campaigns as $campaign) {
            $this->processCampaign($campaign);
        }
        
        return 0;
    }
    
    /**
     * Process a single campaign.
     *
     * @param  \App\Models\Campaign  $campaign
     * @return void
     */
    protected function processCampaign(Campaign $campaign)
    {
        $this->info("Processing campaign: {$campaign->name} (ID: {$campaign->id})");

        // A due scheduled campaign becomes running for the length of this pass, so
        // the state on screen matches what is happening.
        if ($campaign->status === Campaign::STATUS_SCHEDULED) {
            $campaign->update(['status' => Campaign::STATUS_RUNNING]);
        }

        // Get recipients based on the audience type
        $recipients = $this->getRecipients($campaign);
        
        if (count($recipients) === 0) {
            $this->warn("No recipients found for this campaign.");
            return;
        }
        
        $this->info("Found " . count($recipients) . " recipients.");
        
        // Update recipient count
        $campaign->recipients_count = count($recipients);
        $campaign->save();
        
        $successCount = 0;
        
        // Process according to campaign type
        if ($campaign->campaign_type === Campaign::TYPE_EMAIL) {
            $successCount = $this->sendEmailCampaign($campaign, $recipients);
        } elseif ($campaign->campaign_type === Campaign::TYPE_SMS) {
            $successCount = $this->sendSmsCampaign($campaign, $recipients);
        } else {
            $this->error("Unknown channel '{$campaign->campaign_type}'. Nothing was sent.");
        }
        
        // Update campaign delivery stats
        $campaign->delivered_count = $successCount;
        $campaign->status = Campaign::STATUS_COMPLETED;
        $campaign->save();
        
        $this->info("Campaign processed successfully. {$successCount} messages sent.");
    }
    
    /**
     * Get recipients for a campaign based on audience type.
     *
     * @param  \App\Models\Campaign  $campaign
     * @return array
     */
    protected function getRecipients(Campaign $campaign)
    {
        $criteria = $campaign->filter_criteria ?? [];

        if ($campaign->audience_type === Campaign::AUDIENCE_EMAILS) {
            return array_map(fn ($email) => [
                'email' => $email,
                'phone' => null,
                'name' => '',
                'participant_id' => null,
            ], array_filter((array) ($criteria['custom_emails'] ?? [])));
        }

        $query = $this->ownedParticipants($campaign);

        if ($campaign->audience_type === Campaign::AUDIENCE_EVENT) {
            // No event means no audience. Falling through to every participant would
            // mail the whole database because of a missing id.
            if (! $campaign->event_id) {
                return [];
            }

            $query->where('event_id', $campaign->event_id);
        }

        if ($campaign->audience_type === Campaign::AUDIENCE_FILTER) {
            $this->applyFilters($query, $criteria);
        }

        return $query->get()
            ->map(fn ($participant) => [
                'email' => $participant->email,
                'phone' => $participant->phone,
                'name' => $participant->name,
                'participant_id' => $participant->id,
            ])
            ->all();
    }

    /**
     * Participants the campaign owner is entitled to contact.
     *
     * This used to be Participant::all(). An organizer sending to "all participants"
     * therefore mailed every participant in the system, including those registered
     * under other accounts' events. Ownership follows the event, so the audience
     * does too.
     */
    protected function ownedParticipants(Campaign $campaign)
    {
        $query = Participant::query()->whereNotNull('email')->where('email', '!=', '');

        $owner = $campaign->user;

        if ($owner && ! $owner->hasRole('Administrator')) {
            $query->whereHas('event', fn ($q) => $q->where('user_id', $owner->id));
        }

        return $query;
    }

    /**
     * Narrow the audience by the saved filter criteria.
     *
     * Age and attendance were stored by the form and then ignored, so the sender
     * silently reached people the filter said it would skip.
     */
    protected function applyFilters($query, array $criteria): void
    {
        if (! empty($criteria['gender'])) {
            $query->where('gender', $criteria['gender']);
        }

        if (! empty($criteria['age'])) {
            [$from, $to] = match ($criteria['age']) {
                '18-24' => [18, 24],
                '25-34' => [25, 34],
                '35-44' => [35, 44],
                '45+' => [45, null],
                default => [null, null],
            };

            if ($from !== null) {
                // Someone aged exactly $from was born on or before today minus $from
                // years; someone aged $to was born after today minus ($to + 1) years.
                $query->whereNotNull('date_of_birth')
                    ->where('date_of_birth', '<=', now()->subYears($from)->toDateString());

                if ($to !== null) {
                    $query->where('date_of_birth', '>', now()->subYears($to + 1)->toDateString());
                }
            }
        }

        if (! empty($criteria['attendance'])) {
            // A check-in is recorded either on the participant row or as an
            // attendance record, depending on how the event was run.
            $hasCheckedIn = function ($q) {
                $q->whereNotNull('attendance_date')
                    ->orWhereExists(function ($sub) {
                        $sub->selectRaw('1')
                            ->from('attendance_records')
                            ->whereColumn('attendance_records.participant_id', 'participants.id')
                            ->whereNotNull('attendance_records.checkin_time');
                    });
            };

            if ($criteria['attendance'] === 'attended') {
                $query->where($hasCheckedIn);
            } elseif ($criteria['attendance'] === 'not_attended') {
                $query->whereNot($hasCheckedIn);
            }
        }
    }
    
    /**
     * Send email campaign to recipients.
     *
     * @param  \App\Models\Campaign  $campaign
     * @param  array  $recipients
     * @return int
     */
    protected function sendEmailCampaign(Campaign $campaign, array $recipients)
    {
        $successCount = 0;
        $content = $campaign->content;
        
        // The owner's own settings, falling back to the Administrator's, which is the
        // rule the rest of the system follows. The old lookup had no user filter at
        // all, so a campaign went out through whichever active email configuration
        // the database happened to return first - possibly another account's.
        [$emailConfig] = \App\Support\DeliveryAccount::emailConfig($campaign->user_id);

        if (!$emailConfig) {
            $this->error("No email configuration for this account or the Administrator.");
            return $successCount;
        }
        
        // Only SMTP used to be handled here, with a "add other providers if needed"
        // note where the rest belonged. An account on Mailgun, SES or sendmail sent
        // its campaigns through whatever mail settings were already loaded.
        if (! \App\Support\MailerConfig::isSupported($emailConfig->provider)) {
            $this->error("Cannot send through '{$emailConfig->provider}'. Nothing was sent.");

            return $successCount;
        }

        ['from_address' => $fromAddress, 'from_name' => $fromName] =
            \App\Support\MailerConfig::apply($emailConfig);

        $this->info("Sending as {$fromName} <{$fromAddress}> via {$emailConfig->provider}.");
        
        // Get email content
        $subject = $content['subject'] ?? 'SIJIL System Notification';
        $emailBody = $content['body'] ?? 'No content provided.';
        
        foreach ($recipients as $recipient) {
            try {
                // Personalize email content if needed
                $personalizedBody = $this->personalizeContent($emailBody, $recipient);
                
                // Add tracking pixel for open rate tracking
                $recipientData = base64_encode(json_encode([
                    'email' => $recipient['email'],
                    'participant_id' => $recipient['participant_id'] ?? null
                ]));
                
                // Replace all links with tracking links
                $personalizedBody = EmailTracker::replaceLinkWithTracking($personalizedBody, $campaign->id, $recipientData);
                
                // Add tracking pixel at the end of the email
                $trackingPixel = '<img src="' . url(route('track.open', ['campaign' => $campaign->id, 'recipient' => $recipientData])) . '" width="1" height="1" alt="" style="display: none;" />';
                
                // Ensure tracking pixel is properly added to HTML content
                if (strpos($personalizedBody, '</body>') !== false) {
                    // If there's a closing body tag, insert before it
                    $personalizedBody = str_replace('</body>', $trackingPixel . '</body>', $personalizedBody);
                } else {
                    // Otherwise append to the end
                    $personalizedBody .= $trackingPixel;
                }
                
                // Send the email
                Mail::html($personalizedBody, function ($message) use ($recipient, $subject, $fromAddress, $fromName) {
                    $message->to($recipient['email'])
                        ->subject($subject)
                        ->from($fromAddress, $fromName);
                });
                
                $successCount++;
                $this->info("Email sent to: {$recipient['email']}");
            } catch (\Exception $e) {
                $this->error("Failed to send email to {$recipient['email']}: " . $e->getMessage());
                Log::error("Email sending error: " . $e->getMessage(), [
                    'campaign_id' => $campaign->id,
                    'recipient' => $recipient['email']
                ]);
            }
        }
        
        return $successCount;
    }
    
    /**
     * Send SMS campaign to recipients.
     *
     * @param  \App\Models\Campaign  $campaign
     * @param  array  $recipients
     * @return int
     */
    protected function sendSmsCampaign(Campaign $campaign, array $recipients)
    {
        // This used to return count($recipients) without sending anything, so a
        // campaign reported full delivery and finished as completed having sent
        // nothing at all.
        $message = $campaign->content['message'] ?? null;

        if (! $message) {
            $this->error('The campaign has no SMS message.');

            return 0;
        }

        // SMS uses the account's own gateway only - there is no fallback, by design.
        [$config] = \App\Support\DeliveryAccount::smsConfig($campaign->user_id);

        if (! $config) {
            $this->error('SMS delivery is switched off for this account. Nothing was sent.');

            return 0;
        }

        $service = new \App\Services\InfobipService();
        $successCount = 0;

        foreach ($recipients as $recipient) {
            if (empty($recipient['phone'])) {
                $this->warn("No phone number for {$recipient['email']}, skipped.");

                continue;
            }

            $result = $service->sendSms(
                $recipient['phone'],
                $this->personalizeContent($message, $recipient),
                $campaign->user_id
            );

            if ($result['success'] ?? false) {
                $successCount++;
                $this->info("SMS sent to: {$recipient['phone']}");

                continue;
            }

            $this->error("Failed to send SMS to {$recipient['phone']}: " . ($result['message'] ?? 'unknown error'));
            Log::error('Campaign SMS error', [
                'campaign_id' => $campaign->id,
                'phone' => $recipient['phone'],
                'message' => $result['message'] ?? null,
            ]);
        }

        return $successCount;
    }
    
    /**
     * Personalize content for specific recipient.
     *
     * @param  string  $content
     * @param  array  $recipient
     * @return string
     */
    protected function personalizeContent($content, array $recipient)
    {
        $content = str_replace('{name}', $recipient['name'] ?? 'Participant', $content);
        $content = str_replace('{email}', $recipient['email'], $content);
        
        return $content;
    }
} 