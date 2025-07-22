<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
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
} 