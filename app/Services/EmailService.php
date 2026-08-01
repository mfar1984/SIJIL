<?php

namespace App\Services;

use App\Models\DeliveryConfig;
use App\Support\DeliveryAccount;
use App\Support\MailerConfig;
use Illuminate\Support\Facades\Mail;
use Exception;

/**
 * Email Service
 *
 * Sends using the account's own Configuration > Delivery settings, falling back
 * to the Administrator's when the account has not set any up. See
 * App\Support\DeliveryAccount for that rule.
 */
class EmailService
{
    /**
     * Send email using user's delivery configuration
     *
     * @param int $userId User ID for configuration
     * @param \Illuminate\Mail\Mailable $mailable Mailable instance
     * @param string $to Recipient email address
     * @return array Response with success status and message
     */
    public function sendEmail($userId, $mailable, $to)
    {
        try {
            [$config, $usedFallback] = DeliveryAccount::emailConfig($userId);

            if (!$config) {
                return [
                    'success' => false,
                    'message' => 'No active email configuration found for this account, and the '
                        . 'Administrator has none either. Set one up under Configuration > Delivery.'
                ];
            }
            
            $settings = $config->settings;
            $provider = $config->provider;
            
            // Configure mail settings based on provider
            $this->configureMailer($provider, $settings);

            // Laravel caches a built mailer per driver name, so rewriting the
            // config alone is not enough: without this, the second and later
            // emails in one request keep using whichever settings the first one
            // built. That matters now that a single registration can send four
            // emails, potentially from different accounts.
            Mail::purge($provider);
            
            // Send the email
            Mail::to($to)->send($mailable);
            
            return [
                'success' => true,
                'message' => $usedFallback
                    ? 'Email sent using the Administrator configuration.'
                    : 'Email sent successfully',
                'used_fallback' => $usedFallback,
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Configure mailer based on provider and settings
     *
     * @param string $provider Email provider (smtp, mailgun, ses, sendmail)
     * @param array $settings Provider settings
     * @return void
     */
    private function configureMailer($provider, $settings)
    {
        // The provider switch lives in App\Support\MailerConfig, which is the one
        // copy. This wrapper keeps the existing call site unchanged.
        MailerConfig::apply(new DeliveryConfig([
            'provider' => $provider,
            'settings' => $settings,
        ]));
    }
}
