<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

class CampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user
        $adminRole = Role::where('name', 'Administrator')->first();
        $admin = User::whereHas('roles', function($query) use ($adminRole) {
            $query->where('id', $adminRole->id);
        })->first();
        
        // Get some organizers
        $organizerRole = Role::where('name', 'Organizer')->first();
        $organizers = User::whereHas('roles', function($query) use ($organizerRole) {
            $query->where('id', $organizerRole->id);
        })->take(2)->get();
        
        // Get some events
        $events = Event::take(3)->get();
        
        // Create email campaign for admin
        Campaign::create([
            'user_id' => $admin->id,
            'name' => 'Welcome Newsletter',
            'description' => 'Welcome email for new participants with important event information and links.',
            'campaign_type' => 'email',
            'audience_type' => 'all_participants',
            'event_id' => null,
            'filter_criteria' => null,
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->subDays(10),
            'status' => 'completed',
            'content' => [
                'subject' => 'Welcome to Annual Conference 2023 - Important Information',
                'body' => '<h3>Welcome to Annual Conference 2023!</h3>
<p>Dear Participant,</p>
<p>We\'re excited to welcome you to our Annual Conference 2023. This email contains important information about the event.</p>
<p><strong>Date:</strong> June 25, 2023</p>
<p><strong>Time:</strong> 9:00 AM - 5:00 PM</p>
<p><strong>Location:</strong> Convention Center, Main Hall</p>
<p>Please don\'t forget to bring your ID and registration confirmation.</p>
<p>We look forward to seeing you!</p>
<p>Best regards,<br>Event Team</p>',
                'include_unsubscribe' => true,
            ],
            'schedule_type' => 'now',
            'scheduled_at' => null,
            'recipients_count' => 245,
            'delivered_count' => 240,
            'opened_count' => 93,
            'clicked_count' => 62,
        ]);
        
        // Create SMS campaign for first organizer
        if ($organizers->count() > 0) {
            Campaign::create([
                'user_id' => $organizers[0]->id,
                'name' => 'Event Reminder',
                'description' => 'SMS reminder sent to participants 24 hours before the event.',
                'campaign_type' => 'sms',
                'audience_type' => 'specific_event',
                'event_id' => $events->first()->id,
                'filter_criteria' => null,
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->subDays(3),
                'status' => 'running',
                'content' => [
                    'message' => 'Reminder: Annual Conference tomorrow at 9AM, Convention Center. Bring your ID. Check-in opens at 8:30AM. Questions? Call 555-1234.',
                    'include_shortlink' => false,
                ],
                'schedule_type' => 'now',
                'scheduled_at' => null,
                'recipients_count' => 120,
                'delivered_count' => 118,
                'opened_count' => 30,
                'clicked_count' => 0,
            ]);
        }
        
        // Create WhatsApp campaign for second organizer
        if ($organizers->count() > 1) {
            Campaign::create([
                'user_id' => $organizers[1]->id,
                'name' => 'Certificate Available',
                'description' => 'WhatsApp notification informing participants that their certificates are ready for download.',
                'campaign_type' => 'whatsapp',
                'audience_type' => 'custom_filter',
                'event_id' => null,
                'filter_criteria' => [
                    'age' => '25-34',
                    'gender' => 'male',
                    'attendance' => 'attended',
                ],
                'start_date' => Carbon::now()->addDays(2),
                'end_date' => null,
                'status' => 'draft',
                'content' => [
                    'message' => 'Hello! Your certificate for Annual Conference 2023 is now available. Click the link below to download it: https://example.com/cert/123456

The certificate will be available for 30 days. Thank you for your participation!',
                ],
                'schedule_type' => 'scheduled',
                'scheduled_at' => Carbon::now()->addDays(2)->setHour(9)->setMinute(0),
                'recipients_count' => 0,
                'delivered_count' => 0,
                'opened_count' => 0,
                'clicked_count' => 0,
            ]);
        }
    }
} 