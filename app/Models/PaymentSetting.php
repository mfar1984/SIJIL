<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The single row of CHIP Collect settings.
 *
 * The secret key is encrypted with APP_KEY by the cast and hidden from every
 * serialisation. The model is never cached and has no LogsActivity trait, so
 * the key cannot reach the cache table or the activity log. Changes are logged
 * by PaymentSettingsController, naming the fields only.
 */
class PaymentSetting extends Model
{
    protected $fillable = [
        'enabled',
        'mode',
        'secret_key',
        'secret_key_last4',
        'brand_id',
        'webhook_public_key',
        'company_public_key',
        'company_public_key_fetched_at',
        'government_email_domains',
        'last_tested_at',
        'last_test_ok',
        'last_test_message',
        'updated_by',
    ];

    protected $hidden = ['secret_key'];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'secret_key' => 'encrypted',
            'last_test_ok' => 'boolean',
            'company_public_key_fetched_at' => 'datetime',
            'last_tested_at' => 'datetime',
        ];
    }

    /**
     * The settings row, read fresh on every call.
     */
    public static function current(): self
    {
        return static::query()->orderBy('id')->first() ?? static::create([
            'enabled' => false,
            'mode' => 'test',
            'government_email_domains' => 'gov.my',
        ]);
    }

    /**
     * A secret key and a Brand ID are stored. The key is tested for presence
     * through its stored hint, so this never decrypts it.
     */
    public function isConfigured(): bool
    {
        return filled($this->getRawOriginal('secret_key')) && filled($this->brand_id);
    }

    public function checkoutEnabled(): bool
    {
        return (bool) $this->enabled && $this->isConfigured();
    }

    /**
     * @return list<string>
     */
    public function governmentDomains(): array
    {
        return static::parseDomains((string) $this->government_email_domains);
    }

    /**
     * Split a newline or comma separated list into lowercase domains, without
     * duplicates or empty entries.
     *
     * @return list<string>
     */
    public static function parseDomains(string $value): array
    {
        $parts = preg_split('/[\s,]+/', strtolower($value)) ?: [];

        return array_values(array_unique(array_filter(array_map(
            fn ($d) => ltrim(trim($d), '.@'),
            $parts
        ))));
    }
}
