<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'participant_id',
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
