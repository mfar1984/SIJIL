<?php

namespace App\Services;

use App\Models\GlobalConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $botToken;
    protected $channelId;
    protected $enabled;

    public function __construct()
    {
        $config = GlobalConfig::getConfig();
        $this->botToken = $config->telegram_bot_token;
        $this->channelId = $config->telegram_channel_id;
        $this->enabled = $config->telegram_event_registration ?? false;
    }

    /**
     * Send message to Telegram channel
     */
    public function sendMessage($message, $parseMode = 'HTML')
    {
        if (!$this->enabled || !$this->botToken || !$this->channelId) {
            Log::info('Telegram notification skipped: Not configured or disabled');
            return false;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $this->channelId,
                'text' => $message,
                'parse_mode' => $parseMode,
            ]);

            if ($response->successful() && $response->json('ok')) {
                Log::info('Telegram notification sent successfully');
                return true;
            } else {
                Log::error('Telegram notification failed', [
                    'response' => $response->json()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Telegram notification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send event registration notification
     */
    public function sendEventRegistrationNotification($participant, $event)
    {
        // Get participant count for this event
        $participantCount = $event->participants()->count();
        
        $message = "🎉 <b>New Event Registration</b>\n\n";
        $message .= "📋 <b>Event:</b> {$event->name}\n";
        $message .= "👤 <b>Participant:</b> {$participant->name}\n";
        $message .= "📧 <b>Email:</b> {$participant->email}\n";
        $message .= "📱 <b>Phone:</b> " . ($participant->phone ?? 'N/A') . "\n";
        $message .= "🏢 <b>Organization:</b> " . ($participant->organization ?? 'N/A') . "\n";
        $message .= "📅 <b>Registered:</b> " . now()->format('d/m/Y H:i:s') . "\n";
        $message .= "🔢 <b>Participant #:</b> {$participantCount} / {$event->max_participants}\n";

        return $this->sendMessage($message);
    }

    /**
     * Send certificate generated notification
     */
    public function sendCertificateGeneratedNotification($participant, $event, $certificate)
    {
        $message = "🎓 <b>Certificate Generated</b>\n\n";
        $message .= "📋 <b>Event:</b> {$event->name}\n";
        $message .= "👤 <b>Participant:</b> {$participant->name}\n";
        $message .= "📧 <b>Email:</b> {$participant->email}\n";
        $message .= "🔢 <b>Certificate No:</b> {$certificate->certificate_number}\n";
        $message .= "📅 <b>Generated:</b> " . now()->format('d/m/Y H:i:s') . "\n";
        $message .= "🏢 <b>Organizer:</b> {$event->organizer}\n";

        return $this->sendMessage($message);
    }

    /**
     * Check if Telegram notifications are enabled
     */
    public function isEnabled()
    {
        return $this->enabled && !empty($this->botToken) && !empty($this->channelId);
    }
}
