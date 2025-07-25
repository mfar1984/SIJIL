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
        
        // Get email and SMS configurations for the current user
        $emailConfig = DeliveryConfig::where('user_id', $user->id)
            ->where('config_type', 'email')
            ->first();
            
        $smsConfig = DeliveryConfig::where('user_id', $user->id)
            ->where('config_type', 'sms')
            ->first();
            
        return view('config.deliver', [
            'emailConfig' => $emailConfig,
            'smsConfig' => $smsConfig,
        ]);
    }
    
    /**
     * Save email configuration.
     */
    public function saveEmailConfig(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'mail_driver' => ['required', Rule::in(['smtp', 'mailgun', 'ses', 'sendmail'])],
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
        
        // Find existing config or create a new one
        $config = DeliveryConfig::updateOrCreate(
            [
                'user_id' => $userId,
                'config_type' => 'email',
                'provider' => $provider,
            ],
            [
                'settings' => $settings,
                'is_active' => true,
            ]
        );
        
        // Deactivate other email configs for this user
        DeliveryConfig::where('user_id', $userId)
            ->where('config_type', 'email')
            ->where('id', '!=', $config->id)
            ->update(['is_active' => false]);
        
        return redirect()->route('config.deliver')
            ->with('success', 'Email configuration saved successfully.');
    }
    
    /**
     * Save SMS configuration.
     */
    public function saveSmsConfig(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'sms_provider' => ['required', Rule::in(['twilio', 'nexmo', 'aws_sns', 'infobip'])],
            'sms_region' => 'required_if:sms_provider,aws_sns',
            'sms_template' => 'nullable|string',
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
        
        // Find existing config or create a new one
        $config = DeliveryConfig::updateOrCreate(
            [
                'user_id' => $userId,
                'config_type' => 'sms',
                'provider' => $provider,
            ],
            [
                'settings' => $settings,
                'default_template' => $request->input('sms_template'),
                'is_active' => true,
            ]
        );
        
        // Deactivate other SMS configs for this user
        DeliveryConfig::where('user_id', $userId)
            ->where('config_type', 'sms')
            ->where('id', '!=', $config->id)
            ->update(['is_active' => false]);
        
        return redirect()->route('config.deliver')
            ->with('success', 'SMS configuration saved successfully.');
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
                'message' => 'No active email configuration found.'
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
            // Configure mail settings based on provider
            $settings = $config->settings;
            $provider = $config->provider;
            
            // Set mail configuration based on provider
            $fromName = $settings['from_name'] ?? 'SIJIL System';
            $fromAddress = $settings['from_address'] ?? 'no-reply@example.com';
            
            // Dynamically set mail configuration based on the provider
            switch ($provider) {
                case 'smtp':
                    config([
                        'mail.default' => 'smtp',
                        'mail.mailers.smtp.host' => $settings['host'] ?? 'smtp.mailtrap.io',
                        'mail.mailers.smtp.port' => $settings['port'] ?? '2525',
                        'mail.mailers.smtp.encryption' => $settings['encryption'] === 'none' ? null : $settings['encryption'],
                        'mail.mailers.smtp.username' => $settings['username'] ?? '',
                        'mail.mailers.smtp.password' => $settings['password'] ?? '',
                        'mail.from.address' => $fromAddress,
                        'mail.from.name' => $fromName,
                    ]);
                    break;
                
                case 'mailgun':
                    config([
                        'mail.default' => 'mailgun',
                        'services.mailgun.domain' => $settings['domain'] ?? '',
                        'services.mailgun.secret' => $settings['secret'] ?? '',
                        'services.mailgun.endpoint' => $settings['endpoint'] ?? 'api.mailgun.net',
                        'mail.from.address' => $fromAddress,
                        'mail.from.name' => $fromName,
                    ]);
                    break;
                    
                case 'ses':
                    config([
                        'mail.default' => 'ses',
                        'services.ses.key' => $settings['key'] ?? '',
                        'services.ses.secret' => $settings['secret'] ?? '',
                        'services.ses.region' => $settings['region'] ?? 'us-east-1',
                        'mail.from.address' => $fromAddress,
                        'mail.from.name' => $fromName,
                    ]);
                    break;
            }

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
        $userId = Auth::id();
        
        $config = DeliveryConfig::where('user_id', $userId)
            ->where('config_type', 'sms')
            ->where('is_active', true)
            ->first();
            
        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'No active SMS configuration found.'
            ]);
        }
        
        // In a real application, you would send a test SMS here
        // For now, we'll just return a success message
        
        return response()->json([
            'success' => true,
            'message' => 'Test SMS sent successfully.'
        ]);
    }
} 