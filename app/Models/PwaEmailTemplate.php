<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PwaEmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'subject',
        'content',
        'scope',
        'user_id',
        'is_active',
        'times_used',
        'last_used_at',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the organizer who owns this template
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who created this template
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this template
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope to get global templates
     */
    public function scopeGlobal($query)
    {
        return $query->where('scope', 'global');
    }

    /**
     * Scope to get organizer templates
     */
    public function scopeOrganizer($query)
    {
        return $query->where('scope', 'organizer');
    }

    /**
     * Scope to get active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get templates by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Increment usage count
     */
    public function incrementUsage()
    {
        $this->increment('times_used');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Replace variables in content
     */
    public function replaceVariables($data)
    {
        $content = $this->content;
        $subject = $this->subject;

        foreach ($data as $key => $value) {
            $content = str_replace('{' . $key . '}', $value, $content);
            $subject = str_replace('{' . $key . '}', $value, $subject);
        }

        return [
            'subject' => $subject,
            'content' => $content
        ];
    }
}
