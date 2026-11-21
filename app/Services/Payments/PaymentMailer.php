<?php

namespace App\Services\Payments;

use App\Services\EmailService;
use App\Support\DeliveryAccount;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends payment emails synchronously (production has no queue worker).
 *
 * 1. Through the Administrator's active email delivery configuration, like
 *    every other email the app sends (EmailService).
 * 2. Without one, through the default mailer only in the local and testing
 *    environments (the array or log mailer of a verification run).
 * 3. Otherwise nothing is sent and false is returned. The .env MAIL_* values
 *    are never used on a server (DeliveryAccount's rule), so a MAIL_MAILER=log
 *    can never make an email look sent.
 *
 * Callers record "sent" only when this returns true.
 */
class PaymentMailer
{
    public static function send(Mailable $mailable, string $to, string $context): bool
    {
        try {
            $administrator = DeliveryAccount::administrator();
            [$config] = $administrator ? DeliveryAccount::emailConfig($administrator->id) : [null];

            if ($config) {
                $result = app(EmailService::class)->sendEmail($administrator->id, $mailable, $to);

                if (! ($result['success'] ?? false)) {
                    Log::warning('Payment mail failed', ['context' => $context]);

                    return false;
                }

                return true;
            }

            if (app()->environment('local', 'testing')) {
                Mail::to($to)->send($mailable);

                return true;
            }

            Log::warning('Payment mail not sent: no active email delivery configuration', ['context' => $context]);

            return false;
        } catch (\Throwable $e) {
            Log::warning('Payment mail failed', ['context' => $context, 'error' => get_class($e)]);

            return false;
        }
    }
}
