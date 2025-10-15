<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixPwaParticipantsMappingSeeder extends Seeder
{
    public function run()
    {
        echo "Starting PWA participants mapping fix...\n";
        
        // Get all PWA participants that need mapping
        $pwaParticipants = DB::table('pwa_participants')->whereNull('related_participant_id')->get();
        echo "Found " . $pwaParticipants->count() . " PWA participants without mapping.\n";
        
        $updatedCount = 0;
        $skippedCount = 0;
        
        foreach ($pwaParticipants as $pwa) {
            $participant = DB::table('participants')->where('email', $pwa->email)->first();
            if ($participant) {
                DB::table('pwa_participants')->where('id', $pwa->id)->update([
                    'related_participant_id' => $participant->id
                ]);
                echo "Mapped PWA participant ID {$pwa->id} to participant ID {$participant->id} (email: {$pwa->email})\n";
                $updatedCount++;
            } else {
                echo "No matching participant found for PWA participant ID {$pwa->id} (email: {$pwa->email})\n";
            }
        }
        
        // Also check for any PWA participants that might have been created with old logic
        $allPwaParticipants = DB::table('pwa_participants')->get();
        echo "Total PWA participants: " . $allPwaParticipants->count() . "\n";
        
        $mappedCount = 0;
        foreach ($allPwaParticipants as $pwa) {
            if (!is_null($pwa->related_participant_id)) {
                $mappedCount++;
            }
        }
        
        echo "Completed! Updated {$updatedCount} PWA participants, {$mappedCount} already mapped.\n";
    }
} 