<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Activity Log entries for the Payment module, under the "payment" log name.
 *
 * A failed or disabled activity log must never undo a payment or an admin
 * action, so every exception is swallowed here. Properties carry references,
 * ids, package codes and amounts, never buyer names or full email addresses.
 */
class PaymentLog
{
    public const LOG_NAME = 'payment';

    /**
     * @param  array<string, mixed>  $properties
     */
    public static function activity(string $description, array $properties = [], ?Model $subject = null, mixed $causer = null): void
    {
        try {
            $logger = activity(self::LOG_NAME)->withProperties($properties);

            if ($subject) {
                $logger->performedOn($subject);
            }

            $causer ??= auth()->user();

            if ($causer) {
                $logger->causedBy($causer);
            }

            $logger->log($description);
        } catch (\Throwable $e) {
            Log::warning('Payment activity log entry not written', [
                'description' => $description,
                'error' => get_class($e),
            ]);
        }
    }
}
