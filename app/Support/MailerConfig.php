<?php

namespace App\Support;

use App\Models\DeliveryConfig;
use Illuminate\Support\Facades\Mail;

/**
 * Points Laravel's mailer at the credentials stored in delivery_configs.
 *
 * This existed seven times over, once in each place that needed to send: two
 * services, a support class, three controllers and a console command. They had
 * drifted apart, and one of them - the campaign sender - only ever handled SMTP.
 * An account configured for Mailgun or SES therefore sent its campaigns through
 * whatever mail settings happened to be loaded, which is nobody's settings in
 * particular.
 *
 * Credentials are never read from the .env MAIL_* values: those point at whatever
 * machine the code is running on, not at the account doing the sending.
 */
class MailerConfig
{
    /**
     * Configure the mailer for this delivery config.
     *
     * @return array{from_address: string, from_name: string, provider: string}
     */
    public static function apply(DeliveryConfig $config): array
    {
        $settings = $config->settings ?? [];
        $provider = $config->provider ?: 'smtp';

        $fromAddress = $settings['from_address'] ?? 'no-reply@example.com';
        $fromName = $settings['from_name'] ?? config('app.name', 'e-Certificate');

        $from = [
            'mail.from.address' => $fromAddress,
            'mail.from.name' => $fromName,
        ];

        switch ($provider) {
            case 'smtp':
                $encryption = $settings['encryption'] ?? null;

                config(array_merge([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.transport' => 'smtp',
                    'mail.mailers.smtp.host' => $settings['host'] ?? '',
                    'mail.mailers.smtp.port' => $settings['port'] ?? 587,
                    'mail.mailers.smtp.encryption' => $encryption === 'none' ? null : $encryption,
                    'mail.mailers.smtp.username' => $settings['username'] ?? '',
                    'mail.mailers.smtp.password' => $settings['password'] ?? '',
                ], $from));
                break;

            case 'mailgun':
                config(array_merge([
                    'mail.default' => 'mailgun',
                    'services.mailgun.domain' => $settings['domain'] ?? '',
                    'services.mailgun.secret' => $settings['secret'] ?? '',
                    'services.mailgun.endpoint' => $settings['endpoint'] ?? 'api.mailgun.net',
                ], $from));
                break;

            case 'ses':
                config(array_merge([
                    'mail.default' => 'ses',
                    'services.ses.key' => $settings['key'] ?? '',
                    'services.ses.secret' => $settings['secret'] ?? '',
                    'services.ses.region' => $settings['region'] ?? 'ap-southeast-1',
                ], $from));
                break;

            case 'sendmail':
                config(array_merge([
                    'mail.default' => 'sendmail',
                    'mail.mailers.sendmail.transport' => 'sendmail',
                    'mail.mailers.sendmail.path' => $settings['path'] ?? '/usr/sbin/sendmail -bs',
                ], $from));
                break;
        }

        // Laravel caches one mailer instance per driver, so without this a second
        // send in the same request or command keeps using the first account's
        // credentials. This is the bug that made the second email in a batch go out
        // from the wrong sender.
        Mail::purge($provider);

        return [
            'from_address' => $fromAddress,
            'from_name' => $fromName,
            'provider' => $provider,
        ];
    }

    /**
     * Whether this provider can actually send.
     *
     * The delivery form accepts four providers and all four are wired up here, so
     * this is really a guard against a row saved before a provider was supported.
     */
    public static function isSupported(?string $provider): bool
    {
        return in_array($provider, ['smtp', 'mailgun', 'ses', 'sendmail'], true);
    }
}
