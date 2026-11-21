<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\User;
use App\Support\SystemSettings;
use Illuminate\Http\Request;

/**
 * BILLING > Payment > Subscriptions: every account that holds a package.
 * Permanent accounts (no expiry) are not listed.
 */
class SubscriptionController extends Controller
{
    public const STATES = ['active' => 'Active', 'expiring' => 'Expiring within 7 days', 'expired' => 'Expired'];

    public function index(Request $request)
    {
        $now = now();
        $soon = now()->addDays(7);

        $query = User::query()
            ->whereNotNull('package_expires_at')
            ->with(['package', 'roles']);

        match ((string) $request->input('state')) {
            'active' => $query->where('package_expires_at', '>', $soon),
            'expiring' => $query->where('package_expires_at', '>', $now)->where('package_expires_at', '<=', $soon),
            'expired' => $query->where('package_expires_at', '<=', $now),
            default => null,
        };

        if ($request->filled('package')) {
            $code = (string) $request->input('package');
            $query->whereHas('package', fn ($q) => $q->where('code', $code));
        }

        if ($request->filled('search')) {
            $search = '%'.addcslashes(trim((string) $request->input('search')), '%_\\').'%';
            $query->where(fn ($q) => $q->where('name', 'LIKE', $search)->orWhere('email', 'LIKE', $search));
        }

        $users = $query->orderBy('package_expires_at')
            ->paginate(SystemSettings::perPage($request))
            ->withQueryString();

        return view('payments.subscriptions.index', [
            'users' => $users,
            'packages' => Package::orderBy('sort_order')->get(),
            'states' => self::STATES,
        ]);
    }

    /**
     * active, expiring or expired, for a stored expiry.
     */
    public static function stateOf(?\DateTimeInterface $expiresAt): ?string
    {
        if ($expiresAt === null) {
            return null;
        }

        $expires = \Illuminate\Support\Carbon::instance($expiresAt);

        return match (true) {
            $expires->lte(now()) => 'expired',
            $expires->lte(now()->addDays(7)) => 'expiring',
            default => 'active',
        };
    }
}
