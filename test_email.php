<?php
require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get the active email configuration
$config = DB::table('delivery_configs')
    ->where('config_type', 'email')
    ->where('is_active', true)
    ->first();

if (!$config) {
    echo "No active email configuration found.\n";
    exit(1);
}

// Parse settings
$settings = json_decode($config->settings, true);
$provider = $config->provider;

// Set mail configuration based on provider
$fromName = $settings['from_name'] ?? 'SIJIL System';
$fromAddress = $settings['from_address'] ?? 'no-reply@example.com';

// Output config
echo "Using the following email configuration:\n";
echo "Provider: {$provider}\n";
echo "Host: {$settings['host']}\n";
echo "Port: {$settings['port']}\n";
echo "Encryption: {$settings['encryption']}\n";
echo "Username: {$settings['username']}\n";
echo "From Address: {$fromAddress}\n";
echo "From Name: {$fromName}\n\n";

// Set mail configuration dynamically
config([
    'mail.default' => 'smtp',
    'mail.mailers.smtp.host' => $settings['host'],
    'mail.mailers.smtp.port' => $settings['port'],
    'mail.mailers.smtp.encryption' => $settings['encryption'] === 'none' ? null : $settings['encryption'],
    'mail.mailers.smtp.username' => $settings['username'],
    'mail.mailers.smtp.password' => $settings['password'],
    'mail.from.address' => $fromAddress,
    'mail.from.name' => $fromName,
]);

// Recipient email
$toEmail = 'faizanrahman84@gmail.com';

// Email content
$subject = 'SIJIL System Test Email';
$message = "This is a test email from SIJIL System sent at " . date('Y-m-d H:i:s') . ".\n\n";
$message .= "If you received this email, your email configuration is working correctly.\n\n";
$message .= "Email configuration details:\n";
$message .= "- Provider: {$provider}\n";
$message .= "- Host: {$settings['host']}\n";
$message .= "- Port: {$settings['port']}\n";
$message .= "- Username: {$settings['username']}\n";

try {
    echo "Sending test email to {$toEmail}...\n";
    
    // Send the email
    Mail::raw($message, function ($mail) use ($toEmail, $subject, $fromName, $fromAddress) {
        $mail->to($toEmail)
             ->subject($subject)
             ->from($fromAddress, $fromName);
    });
    
    echo "Test email sent successfully!\n";
} catch (\Exception $e) {
    echo "Error sending email: " . $e->getMessage() . "\n";
    exit(1);
} 