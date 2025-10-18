<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PwaParticipant;
use App\Models\EventRegistration;
use App\Models\DeliveryConfig;

$email = 'faizanrahman84@gmail.com';

echo "=== TESTING RESET PASSWORD FLOW ===" . PHP_EOL . PHP_EOL;

// 1. Find participant
$participant = PwaParticipant::where('email', $email)->first();
if (!$participant) {
    echo "❌ Participant not found for email: $email" . PHP_EOL;
    exit(1);
}
echo "✅ Participant found: {$participant->name} (ID: {$participant->id})" . PHP_EOL;

// 2. Find event registration
$registration = EventRegistration::where('pwa_participant_id', $participant->id)
    ->with('event.user')
    ->orderBy('created_at', 'desc')
    ->first();

if (!$registration) {
    echo "❌ No event registration found for participant" . PHP_EOL;
    exit(1);
}
echo "✅ Event registration found: {$registration->event->name}" . PHP_EOL;

// 3. Find organizer user
$organizerUser = $registration?->event?->user;
if (!$organizerUser) {
    echo "❌ Event organizer user not found" . PHP_EOL;
    exit(1);
}
echo "✅ Organizer user found: {$organizerUser->name} (ID: {$organizerUser->id})" . PHP_EOL;

// 4. Find DeliveryConfig
$config = DeliveryConfig::getEmailConfig($organizerUser->id);
if (!$config) {
    echo "❌ No DeliveryConfig found for organizer" . PHP_EOL;
    exit(1);
}
echo "✅ DeliveryConfig found: Provider = {$config->provider}" . PHP_EOL;
echo "   Settings: " . json_encode($config->settings, JSON_PRETTY_PRINT) . PHP_EOL;

// 5. Check PwaEmailTemplate
$template = \App\Models\PwaEmailTemplate::query()
    ->where('type', 'password_reset')
    ->where(function($q) use ($organizerUser) {
        $q->where('scope', 'organizer')->where('user_id', $organizerUser->id);
        $q->orWhere('scope', 'global');
    })
    ->orderByRaw("CASE WHEN scope='organizer' THEN 0 ELSE 1 END")
    ->first();

if ($template) {
    echo "✅ Email template found: {$template->subject}" . PHP_EOL;
} else {
    echo "⚠️  No template found, will use default" . PHP_EOL;
}

