<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PwaParticipant;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\Hash;

class PwaParticipantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test PWA participants
        $participants = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => Hash::make('password123'),
                'phone' => '0123456789',
                'organization' => 'ABC Corporation',
                'job_title' => 'Software Engineer',
                'status' => 'active',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => Hash::make('password123'),
                'phone' => '0123456790',
                'organization' => 'XYZ Company',
                'job_title' => 'Marketing Manager',
                'status' => 'active',
            ],
            [
                'name' => 'Ahmad Ali',
                'email' => 'ahmad@example.com',
                'password' => Hash::make('password123'),
                'phone' => '0123456791',
                'organization' => 'Tech Solutions',
                'job_title' => 'Project Manager',
                'status' => 'active',
            ],
        ];

        foreach ($participants as $participantData) {
            $participant = PwaParticipant::create($participantData);
            
            // Register for some events (if events exist)
            $events = Event::where('status', 'active')->take(2)->get();
            
            foreach ($events as $event) {
                EventRegistration::create([
                    'pwa_participant_id' => $participant->id,
                    'event_id' => $event->id,
                    'registration_date' => now(),
                    'status' => 'registered',
                ]);
            }
        }

        $this->command->info('PWA Participants seeded successfully!');
    }
}
