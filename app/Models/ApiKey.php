<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A credential for a server-to-server integration.
 *
 * Deliberately separate from Sanctum personal access tokens. Those belong to a
 * participant signing in to the app; these belong to another system pulling
 * data. Revoking a key must never sign a participant out, and signing a
 * participant out must never break an integration.
 */
class ApiKey extends Model
{
    protected $fillable = [
        'name',
        'key_prefix',
        'key_hash',
        'abilities',
        'created_by',
        'expires_at',
        'revoked_at',
    ];

    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * The plaintext secret is never stored, so it must never be serialised
     * anywhere either. It is held in memory only long enough to show once.
     */
    protected $hidden = ['key_hash'];

    public const PREFIX = 'sk_';

    /**
     * Every ability a key may be granted. A key with none can authenticate but
     * read nothing, which is the safe default rather than full access.
     *
     * @return array<string, string>
     */
    public static function availableAbilities(): array
    {
        return [
            'events.read' => 'Read events',
            'participants.read' => 'Read participants',
            'certificates.read' => 'Read certificates',
        ];
    }

    /**
     * Mint a new key. Returns the model and the one and only copy of the
     * plaintext secret; there is no way to recover it afterwards.
     *
     * @param array<int, string> $abilities
     * @return array{key: self, secret: string}
     */
    public static function mint(
        string $name,
        array $abilities,
        ?int $createdBy = null,
        ?\DateTimeInterface $expiresAt = null
    ): array {
        // 40 random characters after the prefix. The prefix is stored in clear so
        // a key can be recognised in the list and so lookup does not have to hash
        // every row in the table.
        $random = Str::random(40);
        $secret = self::PREFIX . $random;

        $key = self::create([
            'name' => $name,
            'key_prefix' => substr($secret, 0, 12),
            'key_hash' => hash('sha256', $secret),
            'abilities' => array_values($abilities),
            'created_by' => $createdBy,
            'expires_at' => $expiresAt,
        ]);

        return ['key' => $key, 'secret' => $secret];
    }

    /**
     * Resolve a presented secret to a usable key, or null.
     *
     * Narrows by prefix first, then compares the hash in constant time, so a
     * timing difference cannot be used to confirm a partial guess.
     */
    public static function findUsable(string $presented): ?self
    {
        $presented = trim($presented);

        if ($presented === '' || ! str_starts_with($presented, self::PREFIX)) {
            return null;
        }

        $candidates = self::query()
            ->where('key_prefix', substr($presented, 0, 12))
            ->whereNull('revoked_at')
            ->get();

        $hash = hash('sha256', $presented);

        foreach ($candidates as $candidate) {
            if (hash_equals($candidate->getRawOriginal('key_hash'), $hash)) {
                return $candidate->hasExpired() ? null : $candidate;
            }
        }

        return null;
    }

    /**
     * Replace the secret on an existing key, keeping its name, abilities and
     * expiry. The old secret stops working immediately.
     *
     * This is the answer to "I lost the key". The alternative people ask for is
     * to keep the plaintext readable for a while, which would put a working
     * credential in the database and in every backup of it, defeating the point
     * of storing only a hash.
     *
     * @return string The new plaintext secret, returned once.
     */
    public function rotate(): string
    {
        $secret = self::PREFIX . Str::random(40);

        $this->forceFill([
            'key_prefix' => substr($secret, 0, 12),
            'key_hash' => hash('sha256', $secret),
            // A rotated key is a working key again, and the counters describe the
            // secret rather than the record, so they start over.
            'revoked_at' => null,
            'last_used_at' => null,
            'last_used_ip' => null,
            'request_count' => 0,
        ])->save();

        return $secret;
    }

    public function hasExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function isActive(): bool
    {
        return ! $this->isRevoked() && ! $this->hasExpired();
    }

    public function can(string $ability): bool
    {
        return in_array($ability, $this->abilities ?? [], true);
    }

    public function recordUse(?string $ip): void
    {
        // Written with a raw increment so concurrent requests do not overwrite
        // each other's count, and without touching updated_at so the timestamp
        // keeps meaning "when the key was last edited".
        $this->newQuery()
            ->whereKey($this->getKey())
            ->update([
                'last_used_at' => now(),
                'last_used_ip' => $ip,
                'request_count' => $this->newQuery()->getConnection()->raw('request_count + 1'),
            ]);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
