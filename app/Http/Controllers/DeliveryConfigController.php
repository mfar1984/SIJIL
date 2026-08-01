<?php

namespace App\Http\Controllers;

use App\Models\DeliveryConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;

class DeliveryConfigController extends Controller
{
    /**
     * Display the delivery configuration page.
     */
    public function index()
    {
        $user = Auth::user();

        $emailConfig = $this->currentConfig($user->id, 'email');
        $smsConfig = $this->currentConfig($user->id, 'sms');

        return view('config.deliver', [
            'emailConfig' => $emailConfig,
            'smsConfig' => $smsConfig,
            // A channel is on when its saved row is the active one. Switching a
            // channel off keeps the credentials on the row so they are still
            // there when it is switched back on.
            'emailEnabled' => (bool) ($emailConfig->is_active ?? false),
            'smsEnabled' => (bool) ($smsConfig->is_active ?? false),
        ]);
    }

    /**
     * The row the form should show for a channel.
     *
     * An account can accumulate one row per provider it has ever saved. The one
     * worth showing is the active one, and failing that the one saved last.
     */
    private function currentConfig(int $userId, string $configType): ?DeliveryConfig
    {
        return DeliveryConfig::where('user_id', $userId)
            ->where('config_type', $configType)
            ->orderByDesc('is_active')
            ->orderByDesc('updated_at')
            ->first();
    }

    /**
     * Save the row for a channel and switch every other provider row off.
     *
     * `is_active` doubles as the channel's on/off switch: exactly one row per
     * channel can be active, and none are active when the channel is off.
     */
    private function storeConfig(int $userId, string $configType, string $provider, array $attributes, bool $enabled): DeliveryConfig
    {
        $config = DeliveryConfig::updateOrCreate(
            [
                'user_id' => $userId,
                'config_type' => $configType,
                'provider' => $provider,
            ],
            $attributes + ['is_active' => $enabled]
        );

        DeliveryConfig::where('user_id', $userId)
            ->where('config_type', $configType)
            ->where('id', '!=', $config->id)
            ->update(['is_active' => false]);

        return $config;
    }
    
    /**
     * Save email configuration.
     */
    public function saveEmailConfig(Request $request)
    {
        $enabled = $request->boolean('is_enabled');

        // A switched-off channel is not going to send anything, so its credentials
        // are allowed to be incomplete. They only have to add up when it is on.
        $rules = [
            'mail_driver' => ['required', Rule::in(['smtp', 'mailgun', 'ses', 'sendmail'])],
            'mail_encryption' => ['nullable', Rule::in(['tls', 'ssl', 'none'])],
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string|max:255',
        ];

        if ($enabled) {
            $rules = array_merge($rules, [
                'mail_host' => 'required_if:mail_driver,smtp',
                'mail_port' => 'required_if:mail_driver,smtp',
                'mail_username' => 'required_if:mail_driver,smtp',
                'mail_password' => 'required_if:mail_driver,smtp',
                'mail_encryption' => ['required_if:mail_driver,smtp', Rule::in(['tls', 'ssl', 'none'])],
                'mail_from_address' => 'required|email',
                'mail_from_name' => 'required|string|max:255',
                'mailgun_domain' => 'required_if:mail_driver,mailgun',
                'mailgun_secret' => 'required_if:mail_driver,mailgun',
                'mailgun_endpoint' => 'required_if:mail_driver,mailgun',
                'ses_key' => 'required_if:mail_driver,ses',
                'ses_secret' => 'required_if:mail_driver,ses',
                'ses_region' => 'required_if:mail_driver,ses',
            ]);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Use the current user's ID
        $userId = Auth::id();
        
        // Prepare settings array based on the mail driver
        $settings = [];
        $provider = $request->input('mail_driver');
        
        switch ($provider) {
            case 'smtp':
                $settings = [
                    'host' => $request->input('mail_host'),
                    'port' => $request->input('mail_port'),
                    'username' => $request->input('mail_username'),
                    'password' => $request->input('mail_password'),
                    'encryption' => $request->input('mail_encryption'),
                    'from_address' => $request->input('mail_from_address'),
                    'from_name' => $request->input('mail_from_name'),
                ];
                break;
                
            case 'mailgun':
                $settings = [
                    'domain' => $request->input('mailgun_domain'),
                    'secret' => $request->input('mailgun_secret'),
                    'endpoint' => $request->input('mailgun_endpoint'),
                    'from_address' => $request->input('mail_from_address'),
                    'from_name' => $request->input('mail_from_name'),
                ];
                break;
                
            case 'ses':
                $settings = [
                    'key' => $request->input('ses_key'),
                    'secret' => $request->input('ses_secret'),
                    'region' => $request->input('ses_region'),
                    'from_address' => $request->input('mail_from_address'),
                    'from_name' => $request->input('mail_from_name'),
                ];
                break;
                
            case 'sendmail':
                $settings = [
                    'path' => '/usr/sbin/sendmail -bs',
                    'from_address' => $request->input('mail_from_address'),
                    'from_name' => $request->input('mail_from_name'),
                ];
                break;
        }
        
        $this->storeConfig($userId, 'email', $provider, ['settings' => $settings], $enabled);

        return redirect()->route('config.deliver')
            ->with('success', $enabled
                ? 'Email settings saved. This account now sends its own email.'
                : 'Email settings saved and the channel switched off. Email for this account will '
                    . 'be sent using the Administrator configuration.');
    }
    
    /**
     * Save SMS configuration.
     */
    public function saveSmsConfig(Request $request)
    {
        $enabled = $request->boolean('is_enabled');

        $rules = [
            'sms_provider' => ['required', Rule::in(['twilio', 'nexmo', 'aws_sns', 'infobip'])],
            'sms_template' => 'nullable|string',
        ];

        if ($enabled) {
            $rules = array_merge($rules, [
                'twilio_sid' => 'required_if:sms_provider,twilio',
                'twilio_token' => 'required_if:sms_provider,twilio',
                'twilio_from' => 'required_if:sms_provider,twilio',
                'nexmo_key' => 'required_if:sms_provider,nexmo',
                'nexmo_secret' => 'required_if:sms_provider,nexmo',
                'nexmo_from' => 'required_if:sms_provider,nexmo',
                'aws_key' => 'required_if:sms_provider,aws_sns',
                'aws_secret' => 'required_if:sms_provider,aws_sns',
                'aws_region' => 'required_if:sms_provider,aws_sns',
                'infobip_key' => 'required_if:sms_provider,infobip',
                'infobip_base_url' => 'required_if:sms_provider,infobip',
                'infobip_from' => 'required_if:sms_provider,infobip',
            ]);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Use the current user's ID
        $userId = Auth::id();
        
        // Prepare settings array based on the SMS provider
        $settings = [];
        $provider = $request->input('sms_provider');
        
        switch ($provider) {
            case 'twilio':
                $settings = [
                    'sid' => $request->input('twilio_sid'),
                    'token' => $request->input('twilio_token'),
                    'from' => $request->input('twilio_from'),
                ];
                break;
                
            case 'nexmo':
                $settings = [
                    'key' => $request->input('nexmo_key'),
                    'secret' => $request->input('nexmo_secret'),
                    'from' => $request->input('nexmo_from'),
                ];
                break;
                
            case 'aws_sns':
                $settings = [
                    'key' => $request->input('aws_key'),
                    'secret' => $request->input('aws_secret'),
                    'region' => $request->input('aws_region'),
                ];
                break;
                
            case 'infobip':
                $settings = [
                    'key' => $request->input('infobip_key'),
                    'base_url' => $request->input('infobip_base_url'),
                    'from' => $request->input('infobip_from'),
                ];
                break;
        }
        
        $this->storeConfig($userId, 'sms', $provider, [
            'settings' => $settings,
            'default_template' => $request->input('sms_template'),
        ], $enabled);

        if (! $enabled) {
            return redirect()->route('config.deliver')
                ->with('success', 'SMS settings saved and the channel switched off. No SMS will be '
                    . 'sent for this account.');
        }

        // Only Infobip has a service class behind it. Saying so here is better than
        // letting the account believe SMS works and finding out when nothing arrives.
        if ($provider !== 'infobip') {
            return redirect()->route('config.deliver')
                ->with('warning', 'SMS settings saved, but only Infobip can currently send. Nothing '
                    . 'will go out while the gateway is set to ' . ucfirst(str_replace('_', ' ', $provider)) . '.');
        }

        return redirect()->route('config.deliver')
            ->with('success', 'SMS settings saved. This account now sends its own SMS.');
    }
    
    /**
     * Send a test email.
     */
    public function sendTestEmail(Request $request)
    {
        // Use the default email from settings
        return $this->sendTestEmailToAddress($request);
    }

    /**
     * Send a test email to a specific address.
     */
    public function sendTestEmailToAddress(Request $request)
    {
        // Validate the request if an email address is provided
        if ($request->has('email_address')) {
            $validator = Validator::make($request->all(), [
                'email_address' => 'required|email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email address provided.'
                ]);
            }
        }

        $userId = Auth::id();
        
        $config = DeliveryConfig::where('user_id', $userId)
            ->where('config_type', 'email')
            ->where('is_active', true)
            ->first();
            
        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Email delivery is switched off for this account. Turn it on and save '
                    . 'before sending a test.'
            ]);
        }

        // Get the recipient email (either from request or use the from_address as fallback)
        $toEmail = $request->input('email_address', $config->settings['from_address'] ?? null);
        
        if (!$toEmail) {
            return response()->json([
                'success' => false,
                'message' => 'No recipient email address specified.'
            ]);
        }

        try {
            $provider = $config->provider;

            // One shared applier. This switch was missing sendmail, so a test from a
            // sendmail account silently used whatever mail settings were loaded.
            ['from_address' => $fromAddress, 'from_name' => $fromName] =
                \App\Support\MailerConfig::apply($config);

            // Send test email
            $subject = 'SIJIL System Test Email';
            $message = 'This is a test email from SIJIL System. If you received this email, your email configuration is working correctly.';
            
            // Send the actual email
            Mail::raw($message, function ($mail) use ($toEmail, $subject, $fromName, $fromAddress) {
                $mail->to($toEmail)
                     ->subject($subject)
                     ->from($fromAddress, $fromName);
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully to ' . $toEmail
            ]);
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Email sending error: ' . $e->getMessage(), [
                'exception' => $e,
                'config' => [
                    'provider' => $provider,
                    'fromAddress' => $fromAddress,
                    'fromName' => $fromName,
                    'toEmail' => $toEmail,
                ]
            ]);
            
            // Determine a user-friendly error message based on the exception
            $errorMessage = $e->getMessage();
            
            // Handle common SMTP errors
            if (strpos($errorMessage, 'Connection could not be established') !== false) {
                $errorMessage = 'Connection to email server failed. Please check your host, port, and network settings.';
            } elseif (strpos($errorMessage, 'Authentication failed') !== false || strpos($errorMessage, 'Invalid credentials') !== false) {
                $errorMessage = 'Authentication failed. Please check your username and password.';
            } elseif (strpos($errorMessage, 'Failed to authenticate on SMTP server') !== false) {
                $errorMessage = 'Failed to authenticate with the SMTP server. Please verify your credentials.';
            } elseif (strpos($errorMessage, 'timeout') !== false) {
                $errorMessage = 'Connection timed out. Please check your email server settings and network connection.';
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $errorMessage,
                'detailed_error' => $e->getMessage() // Include the detailed error message for debugging
            ]);
        }
    }
    
    /**
     * Send a test SMS.
     */
    public function sendTestSms(Request $request)
    {
        $request->validate([
            'test_phone' => 'required|string',
        ]);
        
        $userId = Auth::id();
        $phoneNumber = $request->input('test_phone');
        
        $config = DeliveryConfig::where('user_id', $userId)
            ->where('config_type', 'sms')
            ->where('is_active', true)
            ->first();
            
        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'SMS delivery is switched off for this account. Turn it on and save '
                    . 'before sending a test.'
            ]);
        }
        
        // Send test SMS using InfobipService
        try {
            $infobipService = new \App\Services\InfobipService();
            $result = $infobipService->sendTestSms($userId, $phoneNumber);
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test SMS: ' . $e->getMessage()
            ]);
        }
    }
} 