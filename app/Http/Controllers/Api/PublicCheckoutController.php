<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\PublicApiCors;
use App\Models\Package;
use App\Models\PaymentOrder;
use App\Models\PaymentSetting;
use App\Services\Payments\CheckoutService;
use App\Services\Payments\PaymentReconciler;
use App\Support\SystemSettings;
use App\Support\UserProfileRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Public checkout for the marketing site's /checkout and /payment/status pages.
 *
 * Called from the visitor's browser across origins, so it is an api route (no
 * session, no CSRF) guarded by the exact Origin allow-list, a honeypot, rate
 * limits and full server-side validation with the User Management rules.
 */
class PublicCheckoutController extends Controller
{
    private const MAX_BODY = 32768;

    public function store(Request $request, CheckoutService $checkout): JsonResponse
    {
        if (strlen((string) $request->getContent()) > self::MAX_BODY) {
            return $this->json(['status' => 'too_large', 'message' => 'The request is too large.'], 413);
        }

        $origin = $request->headers->get('Origin');

        if (! is_string($origin) || ! in_array($origin, PublicApiCors::allowedOrigins(), true)) {
            Log::info('Checkout refused: origin not allowed', ['path' => $request->path()]);

            return $this->json(['status' => 'forbidden', 'message' => 'Requests from this site are not accepted.'], 403);
        }

        if (! PaymentSetting::current()->checkoutEnabled()) {
            return $this->json(['status' => 'unavailable', 'message' => 'Online payment is not available right now. Please contact us.'], 503);
        }

        if (filled($request->input('hp_company'))) {
            Log::info('Checkout honeypot hit');

            return $this->json(['status' => 'invalid', 'message' => 'We could not process this request.'], 422);
        }

        $input = $request->only(array_merge(UserProfileRules::FIELDS, ['package', 'consent']));

        if (is_string($input['email'] ?? null)) {
            $input['email'] = strtolower(trim($input['email']));
        }

        $validator = Validator::make($input, UserProfileRules::rules() + [
            'package' => ['required', 'string', 'regex:/^(normal|government)-(01|03|06|12)m$/'],
            'consent' => ['accepted'],
        ], [
            'package.*' => 'Select a valid package.',
            'consent.accepted' => 'Please accept the terms and privacy policy to continue.',
        ]);

        if ($validator->fails()) {
            return $this->json([
                'status' => 'invalid',
                'message' => 'Please check the highlighted fields.',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $result = $checkout->start($validator->validated(), (string) $request->ip());

        return $this->json($result['body'], $result['status']);
    }

    public function show(string $reference, PaymentReconciler $reconciler): JsonResponse
    {
        $order = PaymentOrder::where('reference', $reference)->first();

        if (! $order) {
            return $this->json(['status' => 'not_found'], 404);
        }

        if ($order->status === PaymentOrder::STATUS_AWAITING_PAYMENT
            && $order->created_at && $order->created_at->lt(now()->subSeconds(5))) {
            $seconds = (int) config('payments.status_recheck_seconds', 20);

            // Atomic claim, so many polling tabs cause at most one CHIP call per window.
            $claimed = DB::table('payment_orders')
                ->where('id', $order->id)
                ->where(fn ($q) => $q->whereNull('last_checked_at')->orWhere('last_checked_at', '<', now()->subSeconds($seconds)))
                ->update(['last_checked_at' => now()]);

            if ($claimed === 1) {
                try {
                    $reconciler->reconcile($order->refresh(), 'recheck');
                } catch (\Throwable $e) {
                    Log::warning('Status page re-check failed', ['order' => $order->reference, 'error' => get_class($e)]);
                }

                $order->refresh();
            }
        }

        $status = match ($order->status) {
            PaymentOrder::STATUS_FULFILLED => 'paid',
            PaymentOrder::STATUS_NEEDS_REVIEW => 'review',
            PaymentOrder::STATUS_FAILED => 'failed',
            PaymentOrder::STATUS_CANCELLED => 'cancelled',
            PaymentOrder::STATUS_EXPIRED => 'expired',
            default => 'pending',
        };

        $message = match ($status) {
            'paid' => $order->type === 'renewal'
                ? 'Payment received. Your package has been renewed.'
                : 'Payment received. Check your inbox for the email to set your password.',
            'review' => 'Payment received. We are checking it and will contact you by email.',
            'failed' => 'The payment could not be completed.',
            'cancelled' => 'The payment was cancelled.',
            'expired' => 'The payment link has expired.',
            default => 'Waiting for payment confirmation.',
        };

        return $this->json([
            'status' => $status,
            'reference' => $order->reference,
            'package_name' => $order->package_name,
            'amount_display' => Package::formatCents((int) $order->amount_cents),
            'is_test' => (bool) $order->is_test,
            'account' => $status === 'paid' ? $order->type : null,
            'expires_on' => $status === 'paid' && $order->new_expires_at ? SystemSettings::date($order->new_expires_at) : null,
            'message' => $message,
        ], 200);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function json(array $body, int $status): JsonResponse
    {
        return response()->json($body, $status, ['Cache-Control' => 'no-store']);
    }
}
