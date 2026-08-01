<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bring the attendance_required flag in line with reality.
 *
 * Events that already had an attendance session were created before the flag
 * existed, so they have attendance to scan but the box unticked - which means
 * participants are never told and the organizer never receives the QR codes.
 *
 * Only turns the flag on. An event with the box ticked but no session yet is a
 * legitimate in-progress state and is left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('attendances') || !Schema::hasColumn('events', 'attendance_required')) {
            return;
        }

        $eventIds = DB::table('attendances')
            ->whereNull('deleted_at')
            ->distinct()
            ->pluck('event_id')
            ->filter()
            ->all();

        if (!$eventIds) {
            return;
        }

        $updated = DB::table('events')
            ->whereIn('id', $eventIds)
            ->where(function ($q) {
                $q->where('attendance_required', false)->orWhereNull('attendance_required');
            })
            ->update(['attendance_required' => true]);

        if ($updated > 0) {
            echo "  synced attendance_required on {$updated} event(s)\n";
        }
    }

    public function down(): void
    {
        // Deliberately not reversed: turning the flag back off would be guessing
        // which events had it set by hand.
    }
};
