<?php

namespace App\Services\Payments;

use App\Models\Package;
use App\Models\PaymentOrder;
use App\Models\PaymentSetting;
use App\Models\User;
use App\Support\GovernmentEmail;
use App\Support\PaymentLog;
use App\Support\PaymentPrivacy;
use App\Support\SecurityPolicy;
use App\Support\UserProfileRules;
use Illuminate\Support\Str;

/**
 * Starts a purchase: account classification, Government eligibility, the
 * order with its price snapshot, and the CHIP purchase.
 *
 * The amount always comes from the package row. The buyer never sends a price
 * or a role.
 */
class CheckoutService
{
    public const PROTECTED_MESSAGE = "We can't start a purchase for this email address. If you already have an e-Certificate account, contact support@e-certificate.com.my.";

    public const GOVERNMENT_MESSAGE = 'Government packages are for email addresses ending in .gov.my. Choose a Normal package or contact us.';

    /**
     * Classify an email for checkout and fulfilment (design §8.3).
     *
     * @return array{type: 'new'|'renewal'|'protected', reason: ?string, user: ?User}
     */
    public static function classify(string $email): array
    {
        return static::classifyUser(User::withTrashed()->where('email', strtolower(trim($email)))->first());
    }

    /**
     * @return array{type: 'new'|'renewal'|'protected', reason: ?string, user: ?User}
     */
    public static function classifyUser(?User $user): array
    {
        if (! $user) {
            return ['type' => 'new', 'reason' => null, 'user' => null];
        }

        $protected = fn (string $reason) => ['type' => 'protected', 'reason' => $reason, 'user' => $user];

        if ($user->trashed()) {
            return $protected('deleted');
        }

        $user->unsetRelation('roles');

        if ($user->hasRole('Administrator')) {
            return $protected('administrator');
        }

        if (! $user->hasRole('Organizer')) {
            return $protected('other_role');
        }

        if (in_array(strtolower((string) ($user->status ?? 'active')), ['inactive', 'banned'], true)) {
            return $protected('inactive');
        }

        if ($user->package_expires_at === null) {
            return $protected('permanent');
        }

        return ['type' => 'renewal', 'reason' => null, 'user' => $user];
    }

    /**
     * Runs checks 6-9 of design §8.2 on validated input.
     *
     * @param  array<string, mixed>  $data  validated input (email already lowercased)
     * @return array{status: int, body: array<string, mixed>}
     */
    public function start(array $data, string $ip): array
    {
        $settings = PaymentSetting::current();
        $ipHash = PaymentPrivacy::hash($ip);
        $email = strtolower(trim((string) $data['email']));

        $package = Package::active()->where('code', $data['package'])->first();

        if (! $package) {
            return $this->invalid(['package' => ['Select a valid package.']]);
        }

        if ($package->category === 'government' && ! GovernmentEmail::matches($email, $settings->governmentDomains())) {
            SecurityPolicy::audit('payment', 'Government package rejected for non-government email', [
                'email_domain' => PaymentPrivacy::emailDomain($email),
                'package' => $package->code,
                'ip_hash' => $ipHash,
            ]);

            return $this->invalid(['email' => [self::GOVERNMENT_MESSAGE]]);
        }

        $class = static::classify($email);

        if ($class['type'] === 'protected') {
            SecurityPolicy::audit('payment', 'Purchase rejected for protected email', [
                'reason' => $class['reason'],
                'email_domain' => PaymentPrivacy::emailDomain($email),
                'package' => $package->code,
                'ip_hash' => $ipHash,
            ], null, $class['user']);

            return $this->invalid(['email' => [self::PROTECTED_MESSAGE]]);
        }

        $snapshot = array_intersect_key($data, array_flip(UserProfileRules::FIELDS));
        $snapshot['email'] = $email;

        $order = PaymentOrder::create([
            'reference' => strtoupper((string) Str::ulid()),
            'type' => $class['type'],
            'status' => PaymentOrder::STATUS_PENDING,
            'package_id' => $package->id,
            'package_code' => $package->code,
            'package_name' => $package->name,
            'package_category' => $package->category,
            'duration_months' => $package->duration_months,
            'amount_cents' => $package->price_cents,
            'currency' => $package->currency ?: 'MYR',
            'email' => $email,
            'buyer_snapshot' => $snapshot,
            'ip_hash' => $ipHash,
            'consent_at' => now(),
            'due_at' => now()->addHours((int) config('payments.order_deadline_hours')),
        ]);

        try {
            $purchase = ChipClient::fromSettings($settings)->createPurchase($this->purchaseBody($order, $settings), $order->reference);
        } catch (ChipException $e) {
            $order->update([
                'status' => PaymentOrder::STATUS_FAILED,
                'failure_reason' => substr('CHIP create failed: HTTP '.$e->httpStatus.($e->chipCode ? ' '.$e->chipCode : ''), 0, 255),
            ]);

            PaymentLog::activity('Payment order could not be started', [
                'reference' => $order->reference,
                'package_code' => $order->package_code,
                'http_status' => $e->httpStatus,
            ], $order, false);

            return ['status' => 503, 'body' => [
                'status' => 'unavailable',
                'message' => 'We could not start the payment. Please try again in a few minutes.',
            ]];
        }

        $checkoutUrl = (string) $purchase['checkout_url'];

        if (! preg_match('/^[0-9a-f-]{36}$/i', (string) $purchase['id']) || ! preg_match('#^https?://#i', $checkoutUrl) || strlen($checkoutUrl) > 500) {
            $order->update([
                'status' => PaymentOrder::STATUS_FAILED,
                'failure_reason' => 'CHIP create failed: unexpected purchase id or checkout URL',
            ]);

            PaymentLog::activity('Payment order could not be started', [
                'reference' => $order->reference,
                'package_code' => $order->package_code,
            ], $order, false);

            return ['status' => 503, 'body' => [
                'status' => 'unavailable',
                'message' => 'We could not start the payment. Please try again in a few minutes.',
            ]];
        }

        $order->update([
            'chip_purchase_id' => strtolower((string) $purchase['id']),
            'checkout_url' => $checkoutUrl,
            'is_test' => (bool) ($purchase['is_test'] ?? ($settings->mode === 'test')),
            'chip_status' => is_string($purchase['status'] ?? null) ? substr($purchase['status'], 0, 32) : null,
            'status' => PaymentOrder::STATUS_AWAITING_PAYMENT,
        ]);

        PaymentLog::activity('Payment order created', [
            'reference' => $order->reference,
            'package_code' => $order->package_code,
            'amount_cents' => $order->amount_cents,
            'type' => $order->type,
            'is_test' => $order->is_test,
        ], $order, false);

        return ['status' => 201, 'body' => [
            'status' => 'ok',
            'reference' => $order->reference,
            'checkout_url' => $checkoutUrl,
        ]];
    }

    /**
     * CHIP PurchaseBody (design §7.3). Callback and redirect URLs are
     * top-level fields.
     *
     * @return array<string, mixed>
     */
    public function purchaseBody(PaymentOrder $order, PaymentSetting $settings): array
    {
        $returnUrl = (string) config('payments.return_url');
        $sep = str_contains($returnUrl, '?') ? '&' : '?';
        $back = $returnUrl.$sep.'ref='.$order->reference;
        $snapshot = $order->buyer_snapshot ?? [];

        return [
            'brand_id' => $settings->brand_id,
            'reference' => $order->reference,
            'client' => [
                'email' => $order->email,
                'full_name' => Str::limit((string) ($snapshot['name'] ?? ''), 128, ''),
            ],
            'purchase' => [
                'currency' => $order->currency,
                'products' => [[
                    'name' => $order->package_name,
                    'price' => (int) $order->amount_cents,
                ]],
                'due_strict' => true,
                'timezone' => 'Asia/Kuala_Lumpur',
                'language' => 'en',
            ],
            'due' => ($order->due_at ?? now()->addHours((int) config('payments.order_deadline_hours')))->getTimestamp(),
            'send_receipt' => true,
            'success_redirect' => $back,
            'failure_redirect' => $back.'&result=failed',
            'cancel_redirect' => $back.'&result=cancelled',
            'success_callback' => route('api.payments.chip.callback'),
            'creator_agent' => 'e-Certificate',
            'platform' => 'web',
        ];
    }

    /**
     * @param  array<string, list<string>>  $errors
     * @return array{status: int, body: array<string, mixed>}
     */
    private function invalid(array $errors): array
    {
        return ['status' => 422, 'body' => [
            'status' => 'invalid',
            'message' => 'Please check the highlighted fields.',
            'errors' => $errors,
        ]];
    }
}
