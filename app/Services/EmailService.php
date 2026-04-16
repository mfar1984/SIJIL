<?php

namespace App\Services;

use App\Models\DeliveryConfig;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Exception;

/**
 * Email Service
 * 
 * This service handles email sending using user-specific delivery configurations.
 * Each organizer can configure their own email provider (SMTP, Mailgun, SES, etc.)
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
            // Get the user's email configuration
            $config = DeliveryConfig::getEmailConfig($userId);
            
            if (!$config) {
                return [
                    'success' => false,
                    'message' => 'No active email configuration found for this user.'
                ];
            }
            
            $settings = $config->settings;
            $provider = $config->provider;
            
            // Configure mail settings based on provider
            $this->configureMailer($provider, $settings);
            
            // Send the email
            Mail::to($to)->send($mailable);
            
            return [
                'success' => true,
                'message' => 'Email sent successfully'
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
        switch ($provider) {
            case 'smtp':
                Config::set('mail.default', 'smtp');
                Config::set('mail.mailers.smtp', [
                    'transport' => 'smtp',
                    'host' => $settings['host'] ?? '',
                    'port' => $settings['port'] ?? 587,
                    'encryption' => $settings['encryption'] ?? 'tls',
                    'username' => $settings['username'] ?? '',
                    'password' => $settings['password'] ?? '',
                    'timeout' => null,
                ]);
                Config::set('mail.from', [
                    'address' => $settings['from_address'] ?? $settings['username'],
                    'name' => $settings['from_name'] ?? 'SIJIL Event Management',
                ]);
                break;
                
            case 'mailgun':
                Config::set('mail.default', 'mailgun');
                Config::set('services.mailgun', [
                    'domain' => $settings['domain'] ?? '',
                    'secret' => $settings['secret'] ?? '',
                    'endpoint' => $settings['endpoint'] ?? 'api.mailgun.net',
                ]);
                Config::set('mail.from', [
                    'address' => $settings['from_address'] ?? '',
                    'name' => $settings['from_name'] ?? 'SIJIL Event Management',
                ]);
                break;
                
            case 'ses':
                Config::set('mail.default', 'ses');
                Config::set('services.ses', [
                    'key' => $settings['key'] ?? '',
                    'secret' => $settings['secret'] ?? '',
                    'region' => $settings['region'] ?? 'us-east-1',
                ]);
                Config::set('mail.from', [
                    'address' => $settings['from_address'] ?? '',
                    'name' => $settings['from_name'] ?? 'SIJIL Event Management',
                ]);
                break;
                
            case 'sendmail':
                Config::set('mail.default', 'sendmail');
                Config::set('mail.mailers.sendmail', [
                    'transport' => 'sendmail',
                    'path' => $settings['path'] ?? '/usr/sbin/sendmail -bs',
                ]);
                Config::set('mail.from', [
                    'address' => $settings['from_address'] ?? '',
                    'name' => $settings['from_name'] ?? 'SIJIL Event Management',
                ]);
                break;
        }
    }
}
