<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'user_id',
        'participant_id',
        'respondent_type',
        'respondent_name',
        'respondent_email',
        'respondent_phone',
        'session_id',
        'response_data',
        'ip_address',
        'user_agent',
        'completed',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'response_data' => 'array',
        'completed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the survey that this response belongs to.
     */
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Get the user that submitted this response (if authenticated).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the participant that submitted this response (if applicable).
     */
    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    /**
     * Get display name for the respondent.
     */
    public function getRespondentDisplayNameAttribute()
    {
        if ($this->user_id) {
            return $this->user->name;
        } elseif ($this->participant_id) {
            return $this->participant->name;
        } elseif ($this->respondent_name) {
            return $this->respondent_name;
        } else {
            return 'Anonymous Respondent';
        }
    }

    /**
     * Get display email for the respondent.
     */
    public function getRespondentDisplayEmailAttribute()
    {
        if ($this->user_id) {
            return $this->user->email;
        } elseif ($this->participant_id) {
            return $this->participant->email;
        } elseif ($this->respondent_email) {
            return $this->respondent_email;
        } else {
            return null;
        }
    }

    /**
     * Get time taken to complete the survey.
     */
    public function getTimeTakenAttribute()
    {
        if (!$this->completed || !$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->completed_at);
    }
}
