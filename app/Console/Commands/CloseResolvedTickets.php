<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HelpdeskTicket;
use App\Models\HelpdeskMessage;
use Carbon\Carbon;

class CloseResolvedTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'helpdesk:close-resolved';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close resolved tickets that have been inactive for 7 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for resolved tickets to close...');
        
        // Find resolved tickets that were last updated more than 7 days ago
        $cutoffDate = Carbon::now()->subDays(7);
        
        // Get all resolved tickets
        $resolvedTickets = HelpdeskTicket::where('status', 'resolved')
            ->where('updated_at', '<', $cutoffDate)
            ->get();
            
        $closedCount = 0;
        
        foreach ($resolvedTickets as $ticket) {
            // Check if there are any messages after the ticket was marked as resolved
            $lastMessage = HelpdeskMessage::where('ticket_id', $ticket->id)
                ->orderBy('created_at', 'desc')
                ->first();
                
            // If the last message is older than 7 days, close the ticket
            if (!$lastMessage || $lastMessage->created_at->lt($cutoffDate)) {
                $ticket->update([
                    'status' => 'closed',
                    'closed_at' => Carbon::now()
                ]);
                
                // Add a system message about auto-closing
                HelpdeskMessage::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => 1, // System user or admin ID
                    'message' => 'This ticket was automatically closed after being resolved for 7 days without activity.',
                    'is_internal' => true,
                ]);
                
                $closedCount++;
                $this->info("Closed ticket #{$ticket->ticket_id}: {$ticket->subject}");
            }
        }
        
        $this->info("Closed {$closedCount} resolved tickets that were inactive for 7 days.");
        
        return Command::SUCCESS;
    }
}
