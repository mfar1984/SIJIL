<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remember which attendance mode the organizer picked.
 *
 * The "Scan once / Scan every day / Let me choose" radio only ever shaped the
 * sessions array in the browser and was thrown away on submit. Nothing could
 * tell afterwards whether one QR code covers the whole event or one per day,
 * which is exactly what the QR email to the organizer needs to know.
 *
 * Existing rows are backfilled from the session data rather than left null.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'attendance_type')) {
                $table->string('attendance_type', 20)
                    ->default('single')
                    ->after('status');
            }
        });

        if (!Schema::hasTable('attendance_sessions')) {
            return;
        }

        // Backfill: a checkout session means the organizer set the windows by
        // hand ('custom'); more than one check-in date means one QR per day.
        foreach (\Illuminate\Support\Facades\DB::table('attendances')->select('id')->get() as $attendance) {
            $sessions = \Illuminate\Support\Facades\DB::table('attendance_sessions')
                ->where('attendance_id', $attendance->id)
                ->get();

            if ($sessions->isEmpty()) {
                continue;
            }

            $hasCheckout = $sessions->contains(fn ($s) => $s->session_type === 'checkout');
            $checkinDays = $sessions->where('session_type', 'checkin')->pluck('date')->unique()->count();

            $type = $hasCheckout ? 'custom' : ($checkinDays > 1 ? 'daily' : 'single');

            \Illuminate\Support\Facades\DB::table('attendances')
                ->where('id', $attendance->id)
                ->update(['attendance_type' => $type]);
        }
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'attendance_type')) {
                $table->dropColumn('attendance_type');
            }
        });
    }
};
