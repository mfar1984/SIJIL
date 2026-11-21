<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A package sold through CHIP Collect or assigned in User Management.
 *
 * The packages table is the only source of the amount charged. code, category
 * and duration_months are fixed by the migration that seeds the eight rows;
 * only the name, the price and the active flag can be edited, so they are the
 * only mass-assignable attributes.
 */
class Package extends Model
{
    public const CATEGORIES = ['normal' => 'Normal', 'government' => 'Government'];

    protected $fillable = ['name', 'price_cents', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'duration_months' => 'integer',
            'price_cents' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * "RM40" for a whole ringgit, "RM40.50" otherwise.
     */
    public static function formatCents(int $cents): string
    {
        $ringgit = intdiv($cents, 100);
        $sen = $cents % 100;

        return 'RM'.number_format($ringgit).($sen ? sprintf('.%02d', $sen) : '');
    }

    public function priceDisplay(): string
    {
        return static::formatCents((int) $this->price_cents);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst((string) $this->category);
    }

    /**
     * "Normal 3 Months — RM65".
     */
    public function label(): string
    {
        return $this->name.' — '.$this->priceDisplay();
    }
}
