<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DummyAttendanceRecordSeeder extends Seeder
{
    public function run()
    {
        // Get attendance for event_id 5
        $attendance = DB::table('attendances')->where('event_id', 5)->first();
        if (!$attendance) return;

        // Get 10 participants for event_id 5
        $participants = DB::table('participants')->where('event_id', 5)->limit(10)->get();
        if ($participants->isEmpty()) return;

        // Get first session for this attendance
        $session = DB::table('attendance_sessions')->where('attendance_id', $attendance->id)->orderBy('date')->first();
        if (!$session) return;

        // Insert attendance_records for each participant
        foreach ($participants as $p) {
            DB::table('attendance_records')->insert([
                'attendance_id' => $attendance->id,
                'attendance_session_id' => $session->id,
                'participant_id' => $p->id,
                'checkin_time' => Carbon::now(),
                'checkout_time' => Carbon::now()->addHours(2),
                'timestamp' => Carbon::now(),
                'status' => 'present',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
} 