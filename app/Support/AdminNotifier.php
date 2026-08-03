<?php

namespace App\Support;

use App\Models\GlobalConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Delivers the administrator notifications the Notifications tab offers.
 *
 * "Notify on system errors" and "Email new users a welcome message" were stored
 * and read by nothing, so both switches did nothing whichever way they were set.
 */
class AdminNotifier
{
    /**
     * How long the same error signature stays suppressed.
     *
     * A failing page can raise the same exception on every request, and an email
     * per occurrence would bury the mailbox and could trip provider rate limits.
     */
    private const ERROR_COOLDOWN_MINUTES = 30;

    public static function recipient(): ?string
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

    /**
     * Report an unhandled exception by email, if that is switched on.
     *
     * Never throws: this runs from the exception handler, where a second failure
     * would replace the original error with a misleading one.
     */
    public static function systemError(\Throwable $exception): void
    {
        try {
            if (! (bool) (GlobalConfig::getConfig()->admin_system_errors ?? false)) {
                return;
            }

            $recipient = static::recipient();

            if (! $recipient) {
                return;
            }

            // Same class and location counts as the same problem.
            $signature = 'admin-error:' . md5(
                get_class($exception) . '|' . $exception->getFile() . '|' . $exception->getLine()
            );

            if (Cache::has($signature)) {
                return;
            }

            Cache::put($signature, true, now()->addMinutes(self::ERROR_COOLDOWN_MINUTES));

            $body = implode(PHP_EOL, [
                get_class($exception),
                '',
                $exception->getMessage(),
                '',
                'File: ' . $exception->getFile() . ':' . $exception->getLine(),
                'URL: ' . (request()?->fullUrl() ?? 'console'),
                'Method: ' . (request()?->method() ?? '-'),
                'User: ' . (auth()->user()?->email ?? 'guest'),
                'IP: ' . (request()?->ip() ?? '-'),
                'Time: ' . now()->toDayDateTimeString(),
                '',
                'Further reports for this same error are suppressed for '
                    . self::ERROR_COOLDOWN_MINUTES . ' minutes.',
            ]);

            Mail::raw($body, function ($message) use ($recipient, $exception) {
                $message->to($recipient)
                    ->subject('[System Error] ' . class_basename($exception));
            });
        } catch (\Throwable $e) {
            Log::warning('Could not email a system error report', ['error' => $e->getMessage()]);
        }
    }
}
