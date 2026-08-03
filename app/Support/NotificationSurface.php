<?php

namespace App\Support;

use App\Models\DeliveryConfig;
use App\Models\GlobalConfig;
use Illuminate\Support\Facades\Schema;

/**
 * Reports which delivery channels can actually carry a notification.
 *
 * The Notifications tab offered email, SMS and Telegram switches with no
 * indication of whether the channel behind each one was configured, so turning
 * one on could achieve nothing without saying so.
 */
class NotificationSurface
{
    /**
     * @return array<string, mixed>
     */
    /**
     * Reporting must never take the settings page down. Reading a channel is a
     * best-effort description of someone else's configuration, so a surprise in
     * that data degrades to "cannot tell" rather than a 500 - which is exactly
     * what a wrong column name caused here.
     *
     * @return array{ready: bool, detail: string}
     */
    private static function safely(callable $probe): array
    {
        try {
            return $probe();
        } catch (\Throwable $e) {
            return ['ready' => false, 'detail' => 'Could not read the delivery configuration.'];
        }
    }

    public static function payload(): array
    {
        return [
            'email' => static::safely(fn () => static::emailChannel()),
            'sms' => static::safely(fn () => static::smsChannel()),
            'telegram' => static::safely(fn () => static::telegramChannel()),
            'recipient' => AdminNotifier::recipient(),
            'recipient_looks_real' => static::recipientLooksReal(),
            'queue' => config('queue.default'),
            'reminder_hours' => (int) (static::config()?->sms_reminder_hours ?? 24),
        ];
    }

    private static function config(): ?GlobalConfig
    {
        try {
            return GlobalConfig::getConfig();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @return array{ready: bool, detail: string}
     */
    private static function emailChannel(): array
    {
        if (! Schema::hasTable('delivery_configs')) {
            return ['ready' => false, 'detail' => 'No delivery configuration table.'];
        }

        // The column is config_type, not type.
        $active = DeliveryConfig::where('config_type', 'email')->where('is_active', true)->count();

        return $active > 0
            ? ['ready' => true, 'detail' => $active . ' active email configuration(s). Accounts without one fall back to the Administrator\'s.']
            : ['ready' => false, 'detail' => 'No active email configuration. Set one under Config > Delivery.'];
    }

    /**
     * @return array{ready: bool, detail: string}
     */
    private static function smsChannel(): array
    {
        if (! Schema::hasTable('delivery_configs')) {
            return ['ready' => false, 'detail' => 'No delivery configuration table.'];
        }

        $configs = DeliveryConfig::where('config_type', 'sms')->where('is_active', true)->get();

        if ($configs->isEmpty()) {
            return ['ready' => false, 'detail' => 'No active SMS configuration. Set one under Config > Delivery.'];
        }

        // Infobip is the only provider with a working implementation; anything
        // else is stored but cannot send, and there is no fallback for SMS the
        // way there is for email.
        $usable = $configs->filter(fn ($config) => strtolower((string) $config->provider) === 'infobip');

        if ($usable->isEmpty()) {
            return [
                'ready' => false,
                'detail' => 'Configured with ' . $configs->pluck('provider')->unique()->implode(', ')
                    . ', which has no implementation here. Only Infobip can send.',
            ];
        }

        return [
            'ready' => true,
            'detail' => $usable->count() . ' account(s) on Infobip. Unlike email there is no Administrator fallback: '
                . 'an organizer without their own SMS configuration sends nothing.',
        ];
    }

    /**
     * @return array{ready: bool, detail: string}
     */
    private static function telegramChannel(): array
    {
        $config = static::config();

        $hasToken = filled($config?->telegram_bot_token);
        $hasChannel = filled($config?->telegram_channel_id);

        if ($hasToken && $hasChannel) {
            return ['ready' => true, 'detail' => 'Bot token and channel are set. Messages go to the channel, not to individuals.'];
        }

        $missing = array_filter([
            $hasToken ? null : 'bot token',
            $hasChannel ? null : 'channel id',
        ]);

        return [
            'ready' => false,
            'detail' => 'Missing the ' . implode(' and ', $missing) . '. Set them on the Telegram tab.',
        ];
    }

    /**
     * The seeded default is admin@sijilevents.com, a domain that does not exist.
     * Worth pointing out, because it is where every administrator notification
     * and security alert is addressed.
     */
    private static function recipientLooksReal(): bool
    {
        $recipient = AdminNotifier::recipient();

        if (! $recipient) {
            return false;
        }

        return ! str_ends_with(strtolower($recipient), '@sijilevents.com');
    }
}
