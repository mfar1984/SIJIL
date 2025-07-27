<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;

class Survey extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'user_id',
        'event_id',
        'status',
        'access_type',
        'allow_anonymous',
        'slug',
        'published_at',
        'expires_at',
    ];

    protected $casts = [
        'allow_anonymous' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::creating(function ($survey) {
            // Make sure we have a unique slug
            if (!$survey->slug) {
                $survey->slug = \Str::slug($survey->title) . '-' . \Str::random(8);
            }
        });
    }

    /**
     * Get the user who created the survey.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event associated with the survey.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the questions for the survey.
     */
    public function questions()
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('order');
    }

    /**
     * Get the responses for the survey.
     */
    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    /**
     * Get the count of completed responses.
     */
    public function getCompletedResponsesCountAttribute()
    {
        return $this->responses()->where('completed', true)->count();
    }

    /**
     * Get the public URL for the survey.
     */
    public function getPublicUrlAttribute()
    {
        return URL::route('public.survey.show', $this->slug);
    }

    /**
     * Check if the survey is published.
     */
    public function getIsPublishedAttribute()
    {
        return $this->status === 'published';
    }

    /**
     * Check if the survey is active (published and not expired).
     */
    public function getIsActiveAttribute()
    {
        if ($this->status !== 'published') {
            return false;
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }

        return true;
    }
}
