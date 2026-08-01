<?php

namespace App\Support;

use App\Models\DeliveryConfig;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Decides whose Configuration > Delivery settings are used to send.
 *
 * Email: an account sends on its own provider when it has one. When it has not
 * enabled one - the normal case for an organizer who never opened the page - the
 * Administrator's configuration is used instead, so the message still goes out
 * rather than disappearing.
 *
 * SMS: own configuration only. No fallback. See smsConfig().
 *
 * Nothing ever falls back to the .env MAIL_* values: those point at whatever
 * machine the code happens to be running on.
 *
 * This lives in one place because the same rule applies to registration
 * confirmations, certificates, attendance QR codes and app welcome emails.
 */
class DeliveryAccount
{
    /**
     * The Administrator account used as the fallback sender.
     */
    public static function administrator(): ?User
    {
        return User::whereHas('roles', fn ($q) => $q->where('name', 'Administrator'))
            ->orderBy('id')
            ->first();
    }

    /**
     * Email configuration for this account, or the Administrator's.
     *
     * @return array{0: DeliveryConfig|null, 1: bool}  [config, usedFallback]
     */
    public static function emailConfig($userId): array
    {
        return self::resolve(
            $userId,
            fn ($id) => DeliveryConfig::getEmailConfig($id),
            'email'
        );
    }

    /**
     * SMS configuration for this account. No fallback.
     *
     * SMS is deliberately different from email: an account that has not enabled
     * the SMS channel simply does not send SMS. Nothing is borrowed from the
     * Administrator, because an SMS goes out from a sender ID the recipient
     * cannot reply to and is billed to whoever owns the gateway - silently
     * spending another account's credit is worse than not sending.
     *
     * Only Infobip is looked for. The delivery form also offers Twilio, Nexmo and
     * AWS SNS, but no service class exists for those, so a config set to one of
     * them cannot send and must not be returned as if it could.
     *
     * @return array{0: DeliveryConfig|null, 1: bool}  [config, usedFallback]
     */
    public static function smsConfig($userId): array
    {
        $config = $userId
            ? DeliveryConfig::where('user_id', $userId)
                ->where('config_type', 'sms')
                ->where('provider', 'infobip')
                ->where('is_active', true)
                ->first()
            : null;

        // The second element stays for callers that destructure the pair, but it
        // is always false now: there is nothing to fall back to.
        return [$config, false];
    }

    /**
     * Own config first, Administrator second.
     *
     * @param  callable(int): (DeliveryConfig|null)  $lookup
     * @return array{0: DeliveryConfig|null, 1: bool}
     */
    protected static function resolve($userId, callable $lookup, string $kind): array
    {
        $own = $userId ? $lookup($userId) : null;

        if ($own) {
            return [$own, false];
        }

        $administrator = self::administrator();

        // Nothing to fall back to when the account with no configuration is the
        // Administrator itself; looking it up again would return the same null.
        if (!$administrator || (int) $administrator->id === (int) $userId) {
            return [null, false];
        }

        $fallback = $lookup($administrator->id);

        if ($fallback) {
            Log::info("Sending {$kind} using the Administrator delivery configuration", [
                'requested_user_id' => $userId,
                'administrator_id' => $administrator->id,
            ]);
        }

        return [$fallback, (bool) $fallback];
    }
}
