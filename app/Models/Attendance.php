<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_id',
        'name',
        'date',
        'start_time',
        'end_time',
        'unique_code',
        'status',
        'attendance_type',
        'created_by',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function records()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function sessions()
    {
        return $this->hasMany(AttendanceSession::class);
    }
}
