<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;

class EventsSeeder extends Seeder
{
    /**
     * Seed events data.
     */
    public function run(): void
    {
        // Get admin and organizer users for user_id
        $adminId = User::whereHas('roles', function ($query) {
            $query->where('name', 'Admin');
        })->first()->id ?? 1;
        
        $organizerId = User::whereHas('roles', function ($query) {
            $query->where('name', 'Organizer');
        })->first()->id ?? 2;
        
        // Create sample events
        $events = [
            [
                'name' => 'Annual Leadership Conference 2025',
                'organizer' => 'Human Resource Division',
                'description' => 'A three-day leadership development conference focusing on emerging leadership skills, team building, and organizational management strategies.',
                'start_date' => '2025-07-21',
                'start_time' => '09:00:00',
                'end_date' => '2025-07-23',
                'end_time' => '17:00:00',
                'location' => 'Kuala Lumpur Convention Center',
                'address' => 'Kuala Lumpur City Centre, 50088 Kuala Lumpur, Malaysia',
                'max_participants' => 200,
                'status' => 'active',
                'user_id' => $adminId,
                'contact_person' => 'Sarah Johnson',
                'contact_email' => 'sarah.johnson@example.com',
                'contact_phone' => '+60123456789',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Digital Transformation Workshop',
                'organizer' => 'IT Department',
                'description' => 'Hands-on workshop exploring digital tools, automation, and technology adoption strategies for organizational efficiency.',
                'start_date' => '2025-08-15',
                'start_time' => '10:00:00',
                'end_date' => '2025-08-16',
                'end_time' => '16:30:00',
                'location' => 'RISDA Training Center',
                'address' => '123 Jalan RISDA, 50000 Kuala Lumpur, Malaysia',
                'max_participants' => 100,
                'status' => 'active',
                'user_id' => $organizerId,
                'contact_person' => 'Michael Lee',
                'contact_email' => 'michael.lee@example.com',
                'contact_phone' => '+60129876543',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Sustainable Agriculture Seminar',
                'organizer' => 'Agriculture Division',
                'description' => 'Seminar on modern sustainable farming practices, renewable resources, and eco-friendly agricultural methods.',
                'start_date' => '2025-09-10',
                'start_time' => '08:30:00',
                'end_date' => '2025-09-10',
                'end_time' => '17:00:00',
                'location' => 'Putrajaya International Convention Centre',
                'address' => 'Dataran Gemilang, Presint 5, 62000 Putrajaya, Malaysia',
                'max_participants' => 250,
                'status' => 'pending',
                'user_id' => $adminId,
                'contact_person' => 'David Wong',
                'contact_email' => 'david.wong@example.com',
                'contact_phone' => '+60132345678',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Financial Management Course',
                'organizer' => 'Finance Department',
                'description' => 'Comprehensive course covering budgeting, financial planning, investment strategies, and financial risk management.',
                'start_date' => '2025-10-05',
                'start_time' => '09:00:00',
                'end_date' => '2025-10-07',
                'end_time' => '16:00:00',
                'location' => 'RISDA Headquarters',
                'address' => '123 Jalan RISDA, 50000 Kuala Lumpur, Malaysia',
                'max_participants' => 50,
                'status' => 'completed',
                'user_id' => $organizerId,
                'contact_person' => 'Robert Chen',
                'contact_email' => 'robert.chen@example.com',
                'contact_phone' => '+60145678901',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        foreach ($events as $eventData) {
            $event = Event::create($eventData);
            
            // Generate registration link
            $event->generateRegistrationLink();
            $event->save();
        }
    }
} 