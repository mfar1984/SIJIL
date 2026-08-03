<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'icon',
        'url',
        'data',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user that owns the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Where this notification points, as a path on this installation.
     *
     * The url column holds whatever route() produced when the row was written,
     * which bakes in APP_URL at that moment. On this database 353 rows point at
     * apps.e-certificate.com.my and 14 at localhost, so following one took you to
     * a different host, where your session does not exist, and you arrived at a
     * login screen. That is the whole of the reported "clicking a notification
     * logs me out".
     *
     * Reduced to a path so the link always stays on the host being used. Anything
     * that is not a path on this application is refused rather than followed.
     */
    public function getSafeUrlAttribute(): string
    {
        $url = trim((string) $this->url);

        if ($url === '' || $url === '#') {
            return '#';
        }

        $path = parse_url($url, PHP_URL_PATH);

        if ($path === false || $path === null || $path === '') {
            return '#';
        }

        $query = parse_url($url, PHP_URL_QUERY);

        // Only ever one leading slash. Two would be read by the browser as a
        // protocol-relative address and send the person to another site, which is
        // the thing this method exists to prevent.
        $safe = '/' . ltrim($path, '/') . ($query ? '?' . $query : '');

        return str_starts_with($safe, '//') ? '#' : $safe;
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Check if notification is unread
     */
    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }
}
