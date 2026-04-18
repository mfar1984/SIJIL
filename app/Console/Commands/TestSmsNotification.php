<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InfobipService;
use App\Models\Event;
use App\Models\Participant;

class TestSmsNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:test {--phone= : Phone number to send test SMS} {--event-id= : Event ID to test with}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test SMS notification for event registration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing SMS Notification...');
        
        // Get phone number from option
        $phone = $this->option('phone');
        
        if (!$phone) {
            $phone = $this->ask('Enter phone number (with country code, e.g., 60123456789)');
        }
        
        if (!$phone) {
            $this->error('Phone number is required.');
            return 1;
        }
        
        // Format phone number
        $phone = ltrim($phone, '+');
        if (substr($phone, 0, 1) === '0') {
            $phone = '60' . substr($phone, 1);
        }
        if (!preg_match('/^60/', $phone)) {
            $phone = '60' . $phone;
        }
        
        $this->info("Using phone number: +{$phone}");
        
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
        
        // Create test message
        $message = "Thank you for registering for {$event->name}. Date: {$event->start_date->format('d/m/Y')} at {$event->location}. We look forward to seeing you!";
        
        $this->info('Message to send:');
        $this->line($message);
        
        if (!$this->confirm('Do you want to send this SMS?', true)) {
            $this->info('SMS sending cancelled.');
            return 0;
        }
        
        // Send SMS
        $this->info('Sending SMS...');
        
        try {
            $infobipService = new InfobipService();
            $result = $infobipService->sendSms($phone, $message, $event->user_id);
            
            if ($result) {
                $this->info('✓ SMS sent successfully!');
                $this->info('Check the phone for the message.');
                return 0;
            } else {
                $this->error('✗ Failed to send SMS.');
                $this->info('Check the logs for more details: storage/logs/laravel.log');
                $this->info('Also check Delivery Config settings for the event organizer.');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());
            return 1;
        }
    }
}
