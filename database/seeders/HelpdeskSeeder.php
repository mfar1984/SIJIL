<?php

namespace Database\Seeders;

use App\Models\HelpdeskTicket;
use App\Models\HelpdeskMessage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HelpdeskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin and organizer users
        $admin = User::whereHas('roles', function($query) {
            $query->where('name', 'Administrator');
        })->first();
        
        $organizers = User::whereHas('roles', function($query) {
            $query->where('name', 'Organizer');
        })->take(3)->get();
        
        if (!$admin || $organizers->isEmpty()) {
            $this->command->error('Admin or organizer users not found. Please run RolesAndPermissionsSeeder first.');
            return;
        }
        
        // Create tickets for each organizer
        foreach ($organizers as $index => $organizer) {
            // Ticket 1: Open ticket
            $ticket1 = HelpdeskTicket::create([
                'ticket_id' => 'HD-' . (1001 + ($index * 3)),
                'subject' => 'Need help with certificate generation',
                'description' => "I'm trying to generate certificates for my event participants but I'm getting an error. Can you help me with this issue?",
                'user_id' => $organizer->id,
                'category' => 'technical',
                'priority' => 'high',
                'status' => 'open',
                'created_at' => Carbon::now()->subDays(rand(1, 5)),
            ]);
            
            // Initial message is the description itself
            HelpdeskMessage::create([
                'ticket_id' => $ticket1->id,
                'user_id' => $organizer->id,
                'message' => $ticket1->description,
                'created_at' => $ticket1->created_at,
            ]);
            
            // Ticket 2: In Progress ticket
            $ticket2 = HelpdeskTicket::create([
                'ticket_id' => 'HD-' . (1002 + ($index * 3)),
                'subject' => 'How to customize event registration form',
                'description' => "I want to add custom fields to my event registration form. Is this possible? If yes, how can I do it?",
                'user_id' => $organizer->id,
                'category' => 'event',
                'priority' => 'medium',
                'status' => 'in_progress',
                'assigned_to' => $admin->id,
                'created_at' => Carbon::now()->subDays(rand(3, 8)),
            ]);
            
            // Initial message
            HelpdeskMessage::create([
                'ticket_id' => $ticket2->id,
                'user_id' => $organizer->id,
                'message' => $ticket2->description,
                'created_at' => $ticket2->created_at,
            ]);
            
            // Admin response
            HelpdeskMessage::create([
                'ticket_id' => $ticket2->id,
                'user_id' => $admin->id,
                'message' => "Hi {$organizer->name}, thanks for reaching out. Yes, you can customize the registration form. Go to Event Settings > Registration Form and you'll see options to add custom fields. Let me know if you need more specific guidance.",
                'created_at' => Carbon::parse($ticket2->created_at)->addHours(rand(2, 8)),
            ]);
            
            // Internal note (only visible to admins)
            HelpdeskMessage::create([
                'ticket_id' => $ticket2->id,
                'user_id' => $admin->id,
                'message' => "This user might need additional training on form customization. Consider scheduling a demo session.",
                'is_internal' => true,
                'created_at' => Carbon::parse($ticket2->created_at)->addHours(rand(2, 8))->addMinutes(15),
            ]);
            
            // Organizer follow-up
            HelpdeskMessage::create([
                'ticket_id' => $ticket2->id,
                'user_id' => $organizer->id,
                'message' => "Thank you for the quick response! I found the settings but I'm having trouble with conditional fields. Can you provide more details on how to set up fields that only appear based on previous answers?",
                'created_at' => Carbon::parse($ticket2->created_at)->addDays(1),
            ]);
            
            // Ticket 3: Resolved ticket
            $ticket3 = HelpdeskTicket::create([
                'ticket_id' => 'HD-' . (1003 + ($index * 3)),
                'subject' => 'Billing question about subscription',
                'description' => "I was charged twice for my monthly subscription. Can you please check and refund the extra payment?",
                'user_id' => $organizer->id,
                'category' => 'billing',
                'priority' => 'low',
                'status' => 'resolved',
                'assigned_to' => $admin->id,
                'created_at' => Carbon::now()->subDays(rand(10, 15)),
                'resolved_at' => Carbon::now()->subDays(rand(5, 9)),
            ]);
            
            // Initial message
            HelpdeskMessage::create([
                'ticket_id' => $ticket3->id,
                'user_id' => $organizer->id,
                'message' => $ticket3->description,
                'created_at' => $ticket3->created_at,
            ]);
            
            // Admin response
            HelpdeskMessage::create([
                'ticket_id' => $ticket3->id,
                'user_id' => $admin->id,
                'message' => "Hello {$organizer->name}, I've checked your billing records and confirmed the double charge. I've processed a refund for the extra payment, which should appear in your account within 3-5 business days. Sorry for the inconvenience!",
                'created_at' => Carbon::parse($ticket3->created_at)->addDays(1),
            ]);
            
            // Organizer thanks
            HelpdeskMessage::create([
                'ticket_id' => $ticket3->id,
                'user_id' => $organizer->id,
                'message' => "Thank you for resolving this so quickly! I appreciate your help.",
                'created_at' => Carbon::parse($ticket3->created_at)->addDays(1)->addHours(2),
            ]);
            
            // Resolution message
            HelpdeskMessage::create([
                'ticket_id' => $ticket3->id,
                'user_id' => $admin->id,
                'message' => "You're welcome! I'm marking this ticket as resolved. Please feel free to reopen it if you have any further questions or if the refund doesn't arrive within the expected timeframe.",
                'created_at' => Carbon::parse($ticket3->created_at)->addDays(1)->addHours(3),
            ]);
        }
    }
}
