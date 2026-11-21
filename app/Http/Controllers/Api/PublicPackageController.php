<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PaymentSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * GET api/public/v1/packages: the active packages and prices for the
 * marketing site's pricing page. Read-only; the site never decides an amount.
 */
class PublicPackageController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $settings = PaymentSetting::current();

            $packages = Package::active()->orderBy('sort_order')->get()->map(fn (Package $p) => [
                'code' => $p->code,
                'name' => $p->name,
                'category' => $p->category,
                'duration_months' => (int) $p->duration_months,
                'price_cents' => (int) $p->price_cents,
            ])->values();

            return response()->json([
                'checkout_enabled' => $settings->checkoutEnabled(),
                'currency' => 'MYR',
                'government_email_domains' => $settings->governmentDomains(),
                'packages' => $packages,
            ], 200, ['Cache-Control' => 'public, max-age=60']);
        } catch (\Throwable $e) {
            Log::error('Public packages endpoint failed', ['error' => get_class($e)]);

            return response()->json(['status' => 'unavailable'], 503, ['Cache-Control' => 'no-store']);
        }
    }
}
