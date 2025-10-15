<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MapPwaParticipantsSeeder extends Seeder
{
    public function run()
    {
        $pwaParticipants = DB::table('pwa_participants')->whereNull('related_participant_id')->get();
        foreach ($pwaParticipants as $pwa) {
            $participant = DB::table('participants')->where('email', $pwa->email)->first();
            if ($participant) {
                DB::table('pwa_participants')->where('id', $pwa->id)->update([
                    'related_participant_id' => $participant->id
                ]);
            }
        }
    }
} 