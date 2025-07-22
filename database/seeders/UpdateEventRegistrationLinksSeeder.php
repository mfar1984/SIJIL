<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;

class UpdateEventRegistrationLinksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all events
        $events = Event::all();
        
        foreach ($events as $event) {
            // Generate a new registration link if needed
            if (empty($event->registration_link)) {
                $event->generateRegistrationLink();
                $event->save();
                
                $this->command->info("Generated registration link for event ID {$event->id}: {$event->name}");
            } else {
                $this->command->info("Event ID {$event->id}: {$event->name} already has a registration link");
            }
        }
        
        $this->command->info("All events now have registration links!");
    }
}
