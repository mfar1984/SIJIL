<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Support\PaymentLog;
use Illuminate\Http\Request;

/**
 * BILLING > Payment > Packages: the eight packages. Name, price and the active
 * flag can be edited; packages are never deleted, because orders and accounts
 * point at them.
 */
class PackageController extends Controller
{
    private const MIN_CENTS = 100;

    private const MAX_CENTS = 10_000_000;

    public function index()
    {
        return view('payments.packages.index', [
            'packages' => Package::orderBy('sort_order')->withCount('users')->get(),
        ]);
    }

    public function update(Request $request, Package $package)
    {
        $validated = $request->validateWithBag('package'.$package->id, [
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'string', 'regex:/^\d{1,6}(\.\d{1,2})?$/', function ($attribute, $value, $fail) {
                $cents = static::toCents((string) $value);

                if ($cents < self::MIN_CENTS || $cents > self::MAX_CENTS) {
                    $fail('The price must be between RM1.00 and RM100,000.00.');
                }
            }],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'price.regex' => 'Enter the price in ringgit, for example 40 or 40.50.',
        ]);

        $new = [
            'name' => trim($validated['name']),
            'price_cents' => static::toCents($validated['price']),
            'is_active' => $request->boolean('is_active'),
        ];

        $old = [
            'name' => $package->name,
            'price_cents' => (int) $package->price_cents,
            'is_active' => (bool) $package->is_active,
        ];

        $changed = array_keys(array_filter($new, fn ($value, $key) => $old[$key] !== $value, ARRAY_FILTER_USE_BOTH));

        if ($changed === []) {
            return redirect()->route('payments.packages.index')
                ->with('success', "No changes to {$package->name}.");
        }

        $package->update($new);

        $labels = array_map(fn ($field) => match ($field) {
            'price_cents' => 'price',
            'is_active' => 'active',
            default => $field,
        }, $changed);

        PaymentLog::activity('Package updated: '.implode(', ', $labels), [
            'code' => $package->code,
            'old' => array_intersect_key($old, array_flip($changed)),
            'new' => array_intersect_key($new, array_flip($changed)),
        ], $package);

        return redirect()->route('payments.packages.index')
            ->with('success', "{$package->name} updated: ".implode(', ', $labels).'.');
    }

    /**
     * "40" → 4000, "40.5" → 4050, "40.50" → 4050, with string arithmetic only.
     */
    public static function toCents(string $value): int
    {
        [$ringgit, $sen] = array_pad(explode('.', trim($value), 2), 2, '');

        return ((int) $ringgit) * 100 + (int) str_pad(substr($sen, 0, 2), 2, '0');
    }
}
