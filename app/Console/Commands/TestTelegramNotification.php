<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;
use App\Models\Event;
use App\Models\Participant;

class TestTelegramNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test {--event-id= : Event ID to test with}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Telegram notification for event registration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Telegram Notification...');
        
        $telegramService = new TelegramService();
        
        // Check if Telegram is enabled
        if (!$telegramService->isEnabled()) {
            $this->error('Telegram notification is not enabled or not configured properly.');
            $this->info('Please check:');
            $this->info('1. Telegram Bot Token is set');
            $this->info('2. Telegram Channel ID is set');
            $this->info('3. Event Registration Notifications is enabled');
            return 1;
        }
        
        $this->info('✓ Telegram is enabled and configured');
        
        // Get event ID from option or use latest event
        $eventId = $this->option('event-id');
        
        if ($eventId) {
            $event = Event::find($eventId);
        } else {
            $event = Event::latest()->first();
        }
        
        if (!$event) {
            $this->error('No event found. Please create an event first.');
            return 1;
        }
        
        $this->info("Using Event: {$event->name} (ID: {$event->id})");
        
        // Get latest participant for this event or create dummy data
        $participant = $event->participants()->latest()->first();
        
        if (!$participant) {
            $this->warn('No participant found for this event. Using dummy data...');
            
            // Create dummy participant object (not saved to DB)
            $participant = new Participant([
                'name' => 'Test Participant',
                'email' => 'test@example.com',
                'phone' => '+60123456789',
                'organization' => 'Test Organization',
            ]);
        } else {
            $this->info("Using Participant: {$participant->name}");
        }
        
        // Send test notification
        $this->info('Sending test notification to Telegram...');
        
        try {
            $result = $telegramService->sendEventRegistrationNotification($participant, $event);
            
            if ($result) {
                $this->info('✓ Telegram notification sent successfully!');
                $this->info('Check your Telegram channel/group for the message.');
                return 0;
            } else {
                $this->error('✗ Failed to send Telegram notification.');
                $this->info('Check the logs for more details: storage/logs/laravel.log');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());
            return 1;
        }
    }
}
