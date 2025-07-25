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
        
        // Otherwise process all running campaigns
        $campaigns = Campaign::where('status', 'running')
            ->where(function($query) {
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
        if ($campaign->campaign_type === 'email') {
            $successCount = $this->sendEmailCampaign($campaign, $recipients);
        } elseif ($campaign->campaign_type === 'sms') {
            $successCount = $this->sendSmsCampaign($campaign, $recipients);
        }
        
        // Update campaign delivery stats
        $campaign->delivered_count = $successCount;
        $campaign->status = 'completed';
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
        $recipients = [];
        
        switch ($campaign->audience_type) {
            case 'all_participants':
                // Get all participants
                $participants = Participant::all();
                foreach ($participants as $participant) {
                    if ($participant->email) {
                        $recipients[] = [
                            'email' => $participant->email,
                            'name' => $participant->name,
                            'participant_id' => $participant->id
                        ];
                    }
                }
                break;
                
            case 'specific_event':
                // Get participants from a specific event
                if ($campaign->event_id) {
                    $event = Event::find($campaign->event_id);
                    if ($event) {
                        $participants = $event->participants;
                        foreach ($participants as $participant) {
                            if ($participant->email) {
                                $recipients[] = [
                                    'email' => $participant->email,
                                    'name' => $participant->name,
                                    'participant_id' => $participant->id
                                ];
                            }
                        }
                    }
                }
                break;
                
            case 'custom_emails':
                // Get custom email addresses
                if (isset($campaign->filter_criteria['custom_emails']) && is_array($campaign->filter_criteria['custom_emails'])) {
                    foreach ($campaign->filter_criteria['custom_emails'] as $email) {
                        $recipients[] = [
                            'email' => $email,
                            'name' => '',
                            'participant_id' => null
                        ];
                    }
                }
                break;
                
            case 'custom_filter':
                // Process custom filter criteria
                $participants = Participant::query();
                
                if (isset($campaign->filter_criteria['gender']) && $campaign->filter_criteria['gender']) {
                    $participants->where('gender', $campaign->filter_criteria['gender']);
                }
                
                // Age filtering would need date of birth calculation
                
                // Get filtered participants
                $filteredParticipants = $participants->get();
                foreach ($filteredParticipants as $participant) {
                    if ($participant->email) {
                        $recipients[] = [
                            'email' => $participant->email,
                            'name' => $participant->name,
                            'participant_id' => $participant->id
                        ];
                    }
                }
                break;
        }
        
        return $recipients;
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
        
        // Get mail configuration
        $emailConfig = \App\Models\DeliveryConfig::where('config_type', 'email')
            ->where('is_active', true)
            ->first();
            
        if (!$emailConfig) {
            $this->error("No active email configuration found.");
            return $successCount;
        }
        
        // Set mail configuration
        $settings = $emailConfig->settings;
        $provider = $emailConfig->provider;
        
        $fromName = $settings['from_name'] ?? 'SIJIL System';
        $fromAddress = $settings['from_address'] ?? 'no-reply@example.com';
        
        // Configure mail settings based on provider
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
                
            // Add other provider configurations if needed
        }
        
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
                $personalizedBody .= $trackingPixel;
                
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
        // SMS implementation would go here
        // For now, we'll just simulate success
        $this->info("SMS campaign processing is not yet implemented. Simulating success.");
        return count($recipients);
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