<?php

namespace App\Support;

use App\Models\PaymentSetting;

/**
 * What the Global Config > Payment tab shows.
 *
 * Never contains the secret key: only whether one is stored and its last four
 * characters, which are kept in their own column so the key is not decrypted
 * to render the page.
 */
class PaymentSurface
{
    public const WEBHOOK_PATH = 'api/payments/chip/webhook';

    public const CALLBACK_PATH = 'api/payments/chip/callback';

    /**
     * @return array<string, mixed>
     */
    public static function payload(?PaymentSetting $settings = null): array
    {
        $settings ??= PaymentSetting::current();

        $hasSecret = filled($settings->getRawOriginal('secret_key'));
        $webhookUrl = url(self::WEBHOOK_PATH);
        $callbackUrl = url(self::CALLBACK_PATH);
        $returnUrl = (string) config('payments.return_url');

        return [
            'enabled' => (bool) $settings->enabled,
            'mode' => $settings->mode === 'live' ? 'live' : 'test',
            'has_secret' => $hasSecret,
            'secret_hint' => $hasSecret ? static::secretHint((string) $settings->secret_key_last4) : null,
            'brand_id' => $settings->brand_id,
            'webhook_public_key' => $settings->webhook_public_key,
            'webhook_key_fingerprint' => static::fingerprint($settings->webhook_public_key),
            'company_key_fetched_at' => $settings->company_public_key_fetched_at
                ? SystemSettings::dateTime($settings->company_public_key_fetched_at)
                : null,
            'government_email_domains' => $settings->governmentDomains(),
            'webhook_url' => $webhookUrl,
            'callback_url' => $callbackUrl,
            'return_url' => $returnUrl,
            'url_warnings' => static::urlWarnings([
                'Webhook URL' => $webhookUrl,
                'Callback URL' => $callbackUrl,
                'Return URL' => $returnUrl,
            ]),
            'last_test' => [
                'at' => $settings->last_tested_at ? SystemSettings::dateTime($settings->last_tested_at) : null,
                'ok' => $settings->last_test_ok,
                'message' => $settings->last_test_message,
            ],
            'can_enable' => $settings->isConfigured(),
            'checkout_enabled' => $settings->checkoutEnabled(),
            'base_url_is_default' => rtrim((string) config('payments.chip.base_url'), '/') === 'https://gate.chip-in.asia/api/v1',
        ];
    }

    /**
     * "•••• abcd". Takes only the stored last four characters.
     */
    public static function secretHint(#[\SensitiveParameter] string $lastFour): string
    {
        return '•••• '.mb_substr($lastFour, -4);
    }

    /**
     * First 16 hex characters of the SHA-256 of the key's DER bytes, so an
     * administrator can compare it with the key shown in the CHIP portal.
     */
    public static function fingerprint(?string $pem): ?string
    {
        if (! is_string($pem) || trim($pem) === '') {
            return null;
        }

        $body = preg_replace('/-----(BEGIN|END)[^-]+-----|\s+/', '', $pem);
        $der = base64_decode((string) $body, true);

        if ($der === false || $der === '') {
            return null;
        }

        return strtoupper(implode(':', str_split(substr(hash('sha256', $der), 0, 16), 2)));
    }

    /**
     * CHIP rejects callback URLs with an explicit port, and only https makes
     * sense for a payment notification.
     *
     * @param  array<string, string>  $urls
     * @return list<string>
     */
    public static function urlWarnings(array $urls): array
    {
        if (app()->environment('local', 'testing')) {
            return [];
        }

        $warnings = [];

        foreach ($urls as $label => $url) {
            $parts = parse_url($url) ?: [];

            if (($parts['scheme'] ?? '') !== 'https') {
                $warnings[] = "{$label} is not https.";
            }

            if (isset($parts['port'])) {
                $warnings[] = "{$label} has an explicit port, which CHIP does not accept.";
            }
        }

        return $warnings;
    }
}
