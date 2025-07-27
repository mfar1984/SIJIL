<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HelpdeskTicket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TestHelpdeskTicketId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'helpdesk:test-ticket-id {--reset : Reset the ticket IDs sequence}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the helpdesk ticket ID generation, especially for IDs exceeding HD-9999 using format HD-DDMMYY-XXXX with alphanumeric codes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('reset')) {
            // Reset the ticket IDs by truncating the table
            if ($this->confirm('This will delete ALL helpdesk tickets. Are you sure?')) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                HelpdeskTicket::truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                $this->info('Helpdesk tickets table has been truncated.');
                
                // Create the first ticket to ensure we start from HD-1001
                $user = User::first();
                if ($user) {
                    $ticket = HelpdeskTicket::create([
                        'subject' => "Initial Ticket",
                        'description' => "This is the initial ticket to set the sequence.",
                        'user_id' => $user->id,
                        'category' => 'technical',
                        'priority' => 'medium',
                    ]);
                    
                    $this->info("Created initial ticket with ID: {$ticket->ticket_id}");
                    
                    // Now manually update it to HD-9999 so the next one will use the new format
                    $ticket->ticket_id = 'HD-9999';
                    $ticket->save();
                    $this->info("Updated initial ticket ID to: {$ticket->ticket_id}");
                }
            } else {
                $this->info('Operation cancelled.');
                return;
            }
        }
        
        // Get the first admin user for creating test tickets
        $user = User::first();
        if (!$user) {
            $this->error('No user found in the database. Please create a user first.');
            return;
        }
        
        // Create a test ticket with ID HD-9999
        $this->info('Creating test tickets...');
        
        // First, find the current highest ticket ID
        $latestTicket = HelpdeskTicket::orderBy('id', 'desc')->first();
        
        // If there are no tickets or the latest ticket ID is not HD-9999
        if (!$latestTicket || $latestTicket->ticket_id === 'HD-9999') {
            // Create a ticket with ID HD-9999
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            HelpdeskTicket::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            $ticket = new HelpdeskTicket([
                'subject' => "Test Ticket HD-9999",
                'description' => "This is a test ticket with ID HD-9999.",
                'user_id' => $user->id,
                'category' => 'technical',
                'priority' => 'medium',
                'status' => 'open',
            ]);
            
            $ticket->ticket_id = 'HD-9999';
            $ticket->save();
            
            $this->info("Created test ticket with ID: {$ticket->ticket_id}");
        } else {
            $this->info("Using existing ticket with ID: {$latestTicket->ticket_id}");
        }
        
        // Now create tickets to demonstrate the transition to the new format
        $this->info('Creating tickets to demonstrate the transition to the new format:');
        
        for ($i = 1; $i <= 5; $i++) {
            $ticket = HelpdeskTicket::create([
                'subject' => "Transition Test Ticket #{$i}",
                'description' => "This is a test ticket to demonstrate the transition in ticket ID format.",
                'user_id' => $user->id,
                'category' => 'technical',
                'priority' => 'medium',
            ]);
            
            $this->info("Created ticket with ID: {$ticket->ticket_id}");
        }
        
        $this->info('Test completed successfully.');
    }
}
