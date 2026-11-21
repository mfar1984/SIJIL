<?php

namespace App\Services\Payments;

use App\Mail\PackageRenewed;
use App\Mail\PurchaseSetPassword;
use App\Models\PaymentOrder;
use App\Models\User;
use App\Support\PaymentLog;
use App\Support\SecurityPolicy;
use App\Support\SystemSettings;
use App\Support\UserProfileRules;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Settles a verified paid purchase: creates the Organizer or extends the
 * existing Organizer's package, in one transaction under a row lock.
 *
 * Idempotent: a second call for a fulfilled order is a no-op returning
 * AlreadyFulfilled. Mails and log entries run only after the commit, so a
 * failed email or a switched-off Activity Log never undoes a paid order.
 */
class OrderFulfilment
{
    /**
     * @param  array<string, mixed>  $purchase  CHIP purchase, already verified
     */
    public function fulfil(PaymentOrder $order, array $purchase, mixed $actor = null): ReconcileOutcome
    {
        $chipStatus = is_string($purchase['status'] ?? null) ? substr($purchase['status'], 0, 32) : null;
        $attempt = fn () => DB::transaction(fn () => $this->fulfilLocked((int) $order->id, $chipStatus), 3);

        try {
            $result = $attempt();
        } catch (UniqueConstraintViolationException) {
            // A parallel transaction created the account for this email first.
            // The second attempt sees it and takes the renewal branch.
            try {
                $result = $attempt();
            } catch (UniqueConstraintViolationException $e) {
                Log::error('Payment fulfilment failed twice on a unique key', ['order' => $order->reference]);

                throw $e;
            }
        }

        $order->refresh();
        $this->afterCommit($order, $result, $actor);

        return $result['outcome'];
    }

    /**
     * @return array{outcome: ReconcileOutcome, kind: string, reasons?: list<string>, user_id?: int}
     */
    private function fulfilLocked(int $orderId, ?string $chipStatus): array
    {
        /** @var PaymentOrder $order */
        $order = PaymentOrder::whereKey($orderId)->lockForUpdate()->firstOrFail();

        if ($order->status === PaymentOrder::STATUS_FULFILLED) {
            return ['outcome' => ReconcileOutcome::AlreadyFulfilled, 'kind' => 'already'];
        }

        if ($order->status === PaymentOrder::STATUS_NEEDS_REVIEW) {
            return ['outcome' => ReconcileOutcome::NeedsReview, 'kind' => 'already_review'];
        }

        if (in_array($order->status, [PaymentOrder::STATUS_CANCELLED, PaymentOrder::STATUS_EXPIRED, PaymentOrder::STATUS_FAILED], true)) {
            return $this->reviewLocked($order, ['Paid after the order was closed ('.$order->status.').'], $chipStatus);
        }

        if (! $order->email) {
            return $this->reviewLocked($order, ['Order has no buyer email (purged).'], $chipStatus);
        }

        $user = User::withTrashed()->where('email', $order->email)->lockForUpdate()->first();
        $class = CheckoutService::classifyUser($user);

        if ($class['type'] === 'protected') {
            return $this->reviewLocked($order, ['Email can no longer be fulfilled safely ('.$class['reason'].').'], $chipStatus);
        }

        $now = now();

        if ($class['type'] === 'new') {
            $role = Role::where('name', 'Organizer')->where('guard_name', 'web')->first();

            if (! $role) {
                Log::error('Payment fulfilment: Organizer role missing', ['order' => $order->reference]);

                return $this->reviewLocked($order, ['Organizer role missing.'], $chipStatus);
            }

            $snapshot = $order->safeSnapshot() ?? [];
            $profile = array_intersect_key($snapshot, array_flip(UserProfileRules::FIELDS));
            unset($profile['email']);

            $user = User::create($profile + [
                'name' => (string) ($snapshot['name'] ?? $order->email),
                'email' => $order->email,
                // Unusable until the buyer sets a password through the emailed link.
                'password' => Hash::make(Str::random(64)),
                'role_id' => $role->id,
                'status' => 'active',
            ]);
            $user->assignRole($role->name);

            $start = $now->copy();
            $expires = $start->copy()->addMonthsNoOverflow((int) $order->duration_months);

            $this->writePackage($user, [
                'package_id' => $order->package_id,
                'package_started_at' => $start,
                'package_expires_at' => $expires,
                'package_source' => 'purchase',
                'password_changed_at' => null,
                'email_verified_at' => null,
                'package_reminder_7d_sent_at' => null,
                'package_reminder_1d_sent_at' => null,
                'package_expired_logged_at' => null,
            ]);

            $previous = null;
            $type = 'new';
        } else {
            $previous = $user->package_expires_at?->copy();
            $base = ($previous && $previous->gt($now)) ? $previous->copy() : $now->copy();
            $expires = $base->copy()->addMonthsNoOverflow((int) $order->duration_months);
            $start = ($previous && $previous->gt($now) && $user->package_started_at) ? $user->package_started_at->copy() : $now->copy();

            $this->writePackage($user, [
                'package_id' => $order->package_id,
                'package_started_at' => $start,
                'package_expires_at' => $expires,
                'package_source' => 'purchase',
                'package_reminder_7d_sent_at' => null,
                'package_reminder_1d_sent_at' => null,
                'package_expired_logged_at' => null,
            ]);

            $type = 'renewal';
        }

        $order->update([
            'type' => $type,
            'status' => PaymentOrder::STATUS_FULFILLED,
            'user_id' => $user->id,
            'paid_at' => $order->paid_at ?? $now,
            'fulfilled_at' => $now,
            'chip_status' => $chipStatus ?? $order->chip_status,
            'previous_expires_at' => $previous,
            'package_started_at' => $start,
            'new_expires_at' => $expires,
        ]);

        return ['outcome' => ReconcileOutcome::Fulfilled, 'kind' => $type, 'user_id' => $user->id];
    }

    /**
     * @param  list<string>  $reasons
     * @return array{outcome: ReconcileOutcome, kind: string, reasons: list<string>}
     */
    private function reviewLocked(PaymentOrder $order, array $reasons, ?string $chipStatus): array
    {
        $order->update([
            'status' => PaymentOrder::STATUS_NEEDS_REVIEW,
            'review_reason' => Str::limit(implode(' ', $reasons), 250, ''),
            'paid_at' => $order->paid_at ?? now(),
            'chip_status' => $chipStatus ?? $order->chip_status,
        ]);

        return ['outcome' => ReconcileOutcome::NeedsReview, 'kind' => 'review', 'reasons' => $reasons];
    }

    /**
     * @param  array{outcome: ReconcileOutcome, kind: string, reasons?: list<string>, user_id?: int}  $result
     */
    private function afterCommit(PaymentOrder $order, array $result, mixed $actor): void
    {
        $causer = $actor ?: false;

        if ($result['kind'] === 'review') {
            static::auditNeedsReview($order, $result['reasons'] ?? []);

            return;
        }

        if (! in_array($result['kind'], ['new', 'renewal'], true)) {
            return;
        }

        $user = User::find($result['user_id']);

        PaymentLog::activity('Payment succeeded', [
            'reference' => $order->reference,
            'package_code' => $order->package_code,
            'amount_cents' => $order->amount_cents,
            'is_test' => $order->is_test,
        ], $order, $causer);

        if (! $user) {
            return;
        }

        if ($result['kind'] === 'new') {
            PaymentLog::activity('Account created from purchase', [
                'reference' => $order->reference,
                'user_id' => $user->id,
                'package_code' => $order->package_code,
                'expires_at' => $order->new_expires_at?->toDateTimeString(),
            ], $user, $causer);

            $this->sendSetPassword($order, $user);
        } else {
            PaymentLog::activity('Package renewed from purchase', [
                'reference' => $order->reference,
                'user_id' => $user->id,
                'package_code' => $order->package_code,
                'previous_expires_at' => $order->previous_expires_at?->toDateTimeString(),
                'expires_at' => $order->new_expires_at?->toDateTimeString(),
            ], $user, $causer);

            PaymentMailer::send(
                new PackageRenewed($user, (string) $order->package_name, SystemSettings::date($order->new_expires_at)),
                $user->email,
                'package-renewed:'.$order->reference
            );
        }
    }

    /**
     * Creates a fresh set-password token (replacing any earlier one) and emails
     * it. Records set_password_sent_at only when the mail went out.
     */
    public function sendSetPassword(PaymentOrder $order, User $user): bool
    {
        try {
            $token = Password::broker('purchases')->createToken($user);
        } catch (\Throwable $e) {
            Log::warning('Set-password token not created', ['order' => $order->reference, 'error' => get_class($e)]);

            return false;
        }

        $sent = PaymentMailer::send(
            new PurchaseSetPassword($user, $token, (string) $order->package_name, SystemSettings::date($order->new_expires_at)),
            $user->email,
            'set-password:'.$order->reference
        );

        if ($sent) {
            $order->forceFill(['set_password_sent_at' => now()])->save();
        }

        return $sent;
    }

    /**
     * Sets an order to needs_review outside a fulfilment (verification
     * mismatch, refund or chargeback). The account is never touched.
     *
     * @param  list<string>  $reasons
     */
    public function markNeedsReview(PaymentOrder $order, array $reasons, ?string $chipStatus = null): ReconcileOutcome
    {
        $changed = DB::transaction(function () use ($order, $reasons, $chipStatus) {
            $locked = PaymentOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (in_array($locked->status, [PaymentOrder::STATUS_FULFILLED, PaymentOrder::STATUS_NEEDS_REVIEW], true)) {
                return false;
            }

            $this->reviewLocked($locked, $reasons, $chipStatus);

            return true;
        });

        $order->refresh();

        if ($changed) {
            static::auditNeedsReview($order, $reasons);

            return ReconcileOutcome::NeedsReview;
        }

        return $order->status === PaymentOrder::STATUS_FULFILLED ? ReconcileOutcome::AlreadyFulfilled : ReconcileOutcome::NeedsReview;
    }

    /**
     * Closes an unpaid order as cancelled or expired, as reported by CHIP.
     */
    public function close(PaymentOrder $order, string $status, ?string $chipStatus, mixed $actor = null): ReconcileOutcome
    {
        $outcome = $status === PaymentOrder::STATUS_CANCELLED ? ReconcileOutcome::Cancelled : ReconcileOutcome::Expired;

        $changed = DB::transaction(function () use ($order, $status, $chipStatus) {
            $locked = PaymentOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! in_array($locked->status, [PaymentOrder::STATUS_PENDING, PaymentOrder::STATUS_AWAITING_PAYMENT], true)) {
                return false;
            }

            $locked->update(['status' => $status, 'chip_status' => $chipStatus ?? $locked->chip_status]);

            return true;
        });

        $order->refresh();

        if ($changed) {
            PaymentLog::activity($status === PaymentOrder::STATUS_CANCELLED ? 'Payment cancelled' : 'Payment expired', [
                'reference' => $order->reference,
                'chip_status' => $chipStatus,
            ], $order, $actor ?: false);

            return $outcome;
        }

        return match ($order->status) {
            PaymentOrder::STATUS_FULFILLED => ReconcileOutcome::AlreadyFulfilled,
            PaymentOrder::STATUS_NEEDS_REVIEW => ReconcileOutcome::NotApplicable,
            default => $outcome,
        };
    }

    /**
     * @param  list<string>  $reasons
     */
    public static function auditNeedsReview(PaymentOrder $order, array $reasons): void
    {
        SecurityPolicy::audit('payment', 'Payment order set to needs review', [
            'reference' => $order->reference,
            'chip_purchase_id' => $order->chip_purchase_id,
            'reasons' => array_values($reasons),
        ], null, $order);
    }

    /**
     * Package columns are not mass assignable; they are written here and in
     * PackageAssignment only, without the User model's own (empty) log entry.
     *
     * @param  array<string, mixed>  $values
     */
    private function writePackage(User $user, array $values): void
    {
        $user->disableLogging();

        try {
            $user->forceFill($values)->save();
        } finally {
            $user->enableLogging();
        }
    }
}
