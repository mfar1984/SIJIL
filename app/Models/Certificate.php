<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Certificate extends Model
{
    use LogsActivity, SoftDeletes, \App\Models\Concerns\FiresWebhooks;

    /**
     * @return array<string, string>
     */
    public function webhookEvents(): array
    {
        return ['created' => 'certificate.generated'];
    }

    /**
     * @return array<string, mixed>
     */
    public function webhookPayload(): array
    {
        return [
            'id' => $this->id,
            'certificate_number' => $this->certificate_number,
            'event_id' => $this->event_id,
            'participant_id' => $this->participant_id,
            'generated_at' => optional($this->generated_at)->toIso8601String(),
        ];
    }
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['participant_id', 'event_id', 'template_id', 'certificate_number', 'status'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Certificate {$eventName}");
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'participant_id',
        'template_id',
        'certificate_number',
        'pdf_file',
        'generated_at',
        'generated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'generated_at' => 'datetime',
    ];

    /**
     * Get the event that the certificate belongs to.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the participant that the certificate belongs to.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    /**
     * Get the template used for the certificate.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'template_id');
    }

    /**
     * Get the user who generated the certificate.
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Generate a unique certificate number.
     */
    public static function generateCertificateNumber(): string
    {
        $prefix = 'CERT';
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        
        return "{$prefix}-{$timestamp}-{$random}";
    }
} 