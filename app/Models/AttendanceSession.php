<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'unique_code',
        'session_type',
        'date',
        'checkin_start_time',
        'checkin_end_time',
        'checkout_start_time',
        'checkout_end_time',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function event()
    {
        // Correctly resolve the Event via the parent Attendance
        return $this->attendance()->first()?->event() ?? $this->belongsTo(Event::class); // fallback to satisfy relation chain
    }
} 