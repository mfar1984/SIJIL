<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class Survey extends Model
{
    use HasFactory, SoftDeletes;

    /** Anyone holding the link may answer. */
    public const AUDIENCE_ANYONE = 'anyone';

    /** Only participants of the linked event may answer. */
    public const AUDIENCE_PARTICIPANTS = 'participants';

    protected $fillable = [
        'title',
        'description',
        'user_id',
        'event_id',
        'status',
        'audience',
        'require_respondent_details',
        'allow_multiple_responses',
        'slug',
        'published_at',
        'opens_at',
        'expires_at',
    ];

    protected $casts = [
        'require_respondent_details' => 'boolean',
        'allow_multiple_responses' => 'boolean',
        'published_at' => 'datetime',
        'opens_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Survey $survey) {
            if (! $survey->slug) {
                $survey->slug = static::generateSlug($survey->title);
            }
        });
    }

    /**
     * Build a unique, URL safe slug for a survey title.
     */
    public static function generateSlug(?string $title): string
    {
        $base = Str::slug((string) $title);

        if ($base === '') {
            $base = 'survey';
        }

        return $base . '-' . Str::lower(Str::random(8));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function questions()
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('order');
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    /**
     * Only submitted responses. Partially filled rows are never persisted, so this
     * exists mainly to keep older data out of the numbers.
     */
    public function completedResponses()
    {
        return $this->responses()->where('completed', true);
    }

    public function getCompletedResponsesCountAttribute(): int
    {
        return $this->completedResponses()->count();
    }

    public function getPublicUrlAttribute(): string
    {
        return URL::route('public.survey.show', $this->slug);
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Whether the survey is currently accepting responses.
     */
    public function isOpen(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        if ($this->opens_at && now()->lt($this->opens_at)) {
            return false;
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }

        return true;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->isOpen();
    }

    /**
     * Why the survey is not accepting responses, or null when it is open.
     */
    public function closedReason(): ?string
    {
        if ($this->status === 'draft') {
            return 'This survey has not been published yet.';
        }

        if ($this->status === 'closed') {
            return 'This survey is closed.';
        }

        if ($this->opens_at && now()->lt($this->opens_at)) {
            return 'This survey opens on ' . $this->opens_at->format('d M Y, H:i') . '.';
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return 'This survey closed on ' . $this->expires_at->format('d M Y, H:i') . '.';
        }

        return null;
    }

    public function isParticipantsOnly(): bool
    {
        return $this->audience === self::AUDIENCE_PARTICIPANTS;
    }

    /**
     * Reasons this survey cannot be published yet, keyed for display in the UI.
     *
     * @return array<int, string>
     */
    public function publishBlockers(): array
    {
        $blockers = [];

        if ($this->questions()->count() === 0) {
            $blockers[] = 'Add at least one question.';
        }

        if ($this->isParticipantsOnly() && ! $this->event_id) {
            $blockers[] = 'Link the survey to an event, or open it to anyone with the link.';
        }

        return $blockers;
    }

    /**
     * Human readable status used across the workspace header and the listing.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'draft') {
            return 'Draft';
        }

        if ($this->status === 'closed') {
            return 'Closed';
        }

        if ($this->opens_at && now()->lt($this->opens_at)) {
            return 'Scheduled';
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return 'Expired';
        }

        return 'Accepting responses';
    }
}
