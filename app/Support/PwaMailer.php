<?php

namespace App\Support;

use App\Helpers\EmailHelper;
use App\Models\DeliveryConfig;
use App\Models\PwaEmailLog;
use App\Models\PwaEmailTemplate;
use App\Models\PwaSetting;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends the transactional emails the PWA relies on (welcome credentials,
 * password resets).
 *
 * The same 120-line block of "configure the mailer from DeliveryConfig, find a
 * template, replace @{{vars}}, send, log" was copied into several controllers
 * and left unimplemented in others. This centralises it so a fix lands
 * everywhere at once.
 */
class PwaMailer
{
    /**
     * Send a templated PWA email.
     *
     * @param  string       $type         PwaEmailTemplate type, e.g. 'welcome'
     * @param  object       $participant  PwaParticipant (needs ->email, ->name)
     * @param  array        $vars         Extra template variables
     * @param  User|null    $sender       Whose DeliveryConfig to use; falls back to Administrator
     * @param  array        $fallback     ['subject' => ..., 'content' => ...] when no template exists
     * @return array{sent: bool, message: string}
     */
    public static function send(
        string $type,
        $participant,
        array $vars = [],
        ?User $sender = null,
        array $fallback = []
    ): array {
        if (empty($participant->email)) {
            return [
                'sent' => false,
                'message' => 'Participant does not have an email address.',
            ];
        }

        $sender = $sender ?: DeliveryAccount::administrator();

        if (!$sender) {
            return [
                'sent' => false,
                'message' => 'No sender account available to send from.',
            ];
        }

        try {
            // This account's own provider, or the Administrator's when it has none.
            [$config, ] = DeliveryAccount::emailConfig($sender->id);

            if (!$config) {
                return [
                    'sent' => false,
                    'message' => 'No active email configuration found for this account or the '
                        . 'Administrator; email was not sent.',
                ];
            }

            $settings = $config->settings ?? [];
            $fromName = $settings['from_name'] ?? 'SIJIL System';
            $fromAddress = $settings['from_address'] ?? 'no-reply@example.com';

            self::applyMailerConfig($config->provider, $settings, $fromName, $fromAddress);

            // A mailer built earlier in this request would otherwise keep its old
            // credentials no matter what we just wrote into the config.
            Mail::purge($config->provider);

            $template = self::resolveTemplate($type, $sender);

            $subject = $template?->subject
                ?: ($fallback['subject'] ?? 'E-Certificate Notification');
            $content = $template?->content
                ?: ($fallback['content'] ?? '<p>Dear @{{name}},</p>');

            // The app URL and support address are configurable under
            // PWA > Settings > Emails, so honour them instead of hardcoding.
            $pwaSettings = PwaSetting::resolveFor($sender);
            $appLink = rtrim((string) ($pwaSettings['pwa_app_link'] ?? ''), '/') ?: url('/pwa');

            $dataVars = array_merge([
                'name' => $participant->name,
                'email' => $participant->email,
                'pwa_link' => $appLink,
                'login_url' => $appLink . '/login',
                'support_email' => $pwaSettings['support_email'] ?? $fromAddress,
                'event_name' => '',
                'organization' => $sender->name ?? 'Organizer',
            ], $vars);

            foreach ($dataVars as $key => $val) {
                $subject = str_replace('@{{' . $key . '}}', (string) $val, $subject);
                $content = str_replace('@{{' . $key . '}}', (string) $val, $content);
            }

            $html = EmailHelper::cleanHtml($content);
            $html = EmailHelper::replaceLinksWithTracking($html, $template->id ?? 0, $participant->email);
            $html = EmailHelper::appendOpenTrackingPixel($html, $template->id ?? 0, $participant->email);

            Mail::html($html, function ($message) use ($participant, $subject, $fromName, $fromAddress) {
                $message->to($participant->email)
                        ->subject($subject)
                        ->from($fromAddress, $fromName);
            });

            if ($template) {
                $template->incrementUsage();
                PwaEmailLog::create([
                    'template_id' => $template->id,
                    'action' => 'sent',
                    'quantity' => 1,
                    'meta' => ['to' => $participant->email, 'context' => $type],
                ]);
            }

            return [
                'sent' => true,
                'message' => 'Email has been sent to ' . $participant->email . '.',
            ];
        } catch (\Throwable $e) {
            Log::error('PWA email failed', [
                'type' => $type,
                'to' => $participant->email,
                'error' => $e->getMessage(),
            ]);

            return [
                'sent' => false,
                'message' => 'Email failed to send: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Pick the template for this type: the sender's own template first, then
     * the global one. Administrators only ever use the global template.
     */
    protected static function resolveTemplate(string $type, User $sender): ?PwaEmailTemplate
    {
        return PwaEmailTemplate::query()
            ->where('type', $type)
            ->where(function ($q) use ($sender) {
                if ($sender->hasRole('Administrator')) {
                    $q->where('scope', 'global');
                } else {
                    $q->where(function ($qq) use ($sender) {
                        $qq->where('scope', 'organizer')->where('user_id', $sender->id);
                    })->orWhere('scope', 'global');
                }
            })
            ->orderByRaw("CASE WHEN scope='organizer' THEN 0 ELSE 1 END")
            ->first();
    }

    /**
     * Point Laravel's mailer at the credentials stored in delivery_configs.
     * These are deliberately not read from .env - each organizer can have
     * their own provider.
     */
    protected static function applyMailerConfig(
        ?string $provider,
        array $settings,
        string $fromName,
        string $fromAddress
    ): void {
        // Kept as a thin wrapper so existing call sites read unchanged. The provider
        // switch itself lives in App\Support\MailerConfig, which is the one copy.
        \App\Support\MailerConfig::apply(new DeliveryConfig([
            'provider' => $provider,
            'settings' => array_merge($settings, [
                'from_address' => $fromAddress,
                'from_name' => $fromName,
            ]),
        ]));
    }

}
