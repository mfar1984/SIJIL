<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory, \App\Models\Concerns\FiresWebhooks;

    /*
     * checkin_time, checkout_time and timestamp are deliberately not cast.
     *
     * They are returned raw in the participant app's JSON, for example by
     * Api\PwaParticipantController::getAttendanceHistory() and scanAttendance().
     * Casting them would change "2026-07-29 09:15:00" into an ISO-8601 string and
     * break a client that deploys separately from this application.
     *
     * Every place that needs date arithmetic already wraps them in Carbon::parse(),
     * which works either way.
     */

    /**
     * @return array<string, string>
     */
    public function webhookEvents(): array
    {
        return ['created' => 'attendance.recorded'];
    }

    /**
     * @return array<string, mixed>
     */
    public function webhookPayload(): array
    {
        /*
         * The columns are checkin_time and checkout_time. This originally read
         * check_in_time and check_out_time, which do not exist, so every
         * attendance.recorded delivery carried two nulls.
         *
         * Parsed rather than accessed as dates: these columns are deliberately not
         * cast, because the participant app reads them raw from the API and a cast
         * would change the format it receives.
         */
        return [
            'id' => $this->id,
            'attendance_id' => $this->attendance_id,
            'attendance_session_id' => $this->attendance_session_id,
            'participant_id' => $this->participant_id,
            'status' => $this->status,
            'checkin_time' => $this->asIso($this->checkin_time),
            'checkout_time' => $this->asIso($this->checkout_time),
        ];
    }

    private function asIso($value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->toIso8601String();
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected $fillable = [
        'attendance_id',
        'attendance_session_id',
        'participant_id',
        'checkin_time',
        'checkout_time',
        'checkin_lat',
        'checkin_lng',
        'checkout_lat',
        'checkout_lng',
        'timestamp',
        'status',
        'scanned_by_device',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function attendanceSession()
    {
        return $this->belongsTo(\App\Models\AttendanceSession::class, 'attendance_session_id');
    }
}
