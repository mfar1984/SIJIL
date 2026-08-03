<?php

namespace App\Support;

use App\Models\GlobalConfig;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Delivers the "Send security alerts to administrators" notifications.
 *
 * That checkbox existed with nothing behind it, so an operator who ticked it
 * received nothing and had no way to discover that.
 */
class SecurityAlert
{
    /**
     * Send an alert, if alerts are switched on.
     *
     * Never throws. A mail failure must not break a sign-in or a settings save.
     *
     * @param array<string, string> $details
     */
    public static function send(string $subject, array $details = []): void
    {
        if (! SecurityPolicy::sendsSecurityAlerts()) {
            return;
        }

        try {
            $recipient = static::recipient();

            if (! $recipient) {
                return;
            }

            $lines = [];

            foreach ($details as $label => $value) {
                $lines[] = $label . ': ' . $value;
            }

            $lines[] = 'Time: ' . now()->toDayDateTimeString();
            $lines[] = 'Site: ' . config('app.url');

            Mail::raw(implode(PHP_EOL, $lines), function ($message) use ($recipient, $subject) {
                $message->to($recipient)
                    ->subject('[Security] ' . $subject);
            });
        } catch (\Throwable $e) {
            Log::warning('Security alert could not be sent', [
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Where alerts go. Falls back through the configured addresses so a missing
     * value does not silently discard the alert.
     */
    private static function recipient(): ?string
    {
        try {
            $config = GlobalConfig::getConfig();
        } catch (\Throwable $e) {
            return null;
        }

        foreach ([$config->admin_notification_email ?? null, $config->org_email ?? null] as $candidate) {
            if (filled($candidate) && filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                return $candidate;
            }
        }

        return null;
    }
}
