<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RealisticAttendanceRecordSeeder extends Seeder
{
    public function run()
    {
        // Get all attendances
        $attendances = DB::table('attendances')->get();
        foreach ($attendances as $attendance) {
            // Get all sessions for this attendance
            $sessions = DB::table('attendance_sessions')->where('attendance_id', $attendance->id)->get();
            // Get up to 10 participants for this event
            $participants = DB::table('participants')->where('event_id', $attendance->event_id)->limit(10)->get();
            if ($participants->isEmpty() || $sessions->isEmpty()) continue;
            foreach ($sessions as $session) {
                foreach ($participants as $p) {
                    // Generate checkin/checkout time based on session times
                    $checkin = $session->checkin_start_time ? Carbon::parse($session->date.' '.$session->checkin_start_time) : Carbon::parse($session->date.' 09:00:00');
                    $checkout = $session->checkout_end_time ? Carbon::parse($session->date.' '.$session->checkout_end_time) : $checkin->copy()->addHours(2);
                    DB::table('attendance_records')->insert([
                        'attendance_id' => $attendance->id,
                        'attendance_session_id' => $session->id,
                        'participant_id' => $p->id,
                        'checkin_time' => $checkin,
                        'checkout_time' => $checkout,
                        'timestamp' => $checkin,
                        'status' => 'present',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
} 