<?php

namespace App\Services\Payments;

use App\Models\PaymentEvent;
use App\Models\PaymentOrder;
use App\Models\PaymentSetting;
use App\Support\PaymentLog;
use App\Support\SecurityPolicy;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * The only path to fulfilment: asks CHIP for the purchase server-side
 * (GET /purchases/{id}/) and acts on that answer. A callback, a webhook, the
 * buyer's status page, an admin Re-check and payments:reconcile all end here;
 * none of them is trusted on its own.
 */
class PaymentReconciler
{
    /** CHIP statuses that mean the money arrived (spec PurchaseStatusBody). */
    public const PAID = ['paid', 'cleared', 'settled'];

    /** Money moved back before we fulfilled: an administrator decides. */
    public const REVIEW = ['refunded', 'pending_refund', 'chargeback'];

    /** A failed attempt; the purchase stays payable until it is due. */
    public const FAILED_ATTEMPT = ['error', 'blocked'];

    public function __construct(private OrderFulfilment $fulfilment)
    {
    }

    public function reconcile(PaymentOrder $order, string $source, ?PaymentEvent $event = null, mixed $actor = null): ReconcileOutcome
    {
        $outcome = $this->check($order, $source, $actor);

        if ($source === 'recheck' && $order->chip_purchase_id) {
            $this->recordRecheck($order, $outcome);
        }

        return $outcome;
    }

    private function check(PaymentOrder $order, string $source, mixed $actor): ReconcileOutcome
    {
        if ($order->status === PaymentOrder::STATUS_FULFILLED) {
            return ReconcileOutcome::AlreadyFulfilled;
        }

        if ($order->status === PaymentOrder::STATUS_NEEDS_REVIEW || ! $order->chip_purchase_id) {
            return ReconcileOutcome::NotApplicable;
        }

        $settings = PaymentSetting::current();

        try {
            $purchase = ChipClient::fromSettings($settings)->getPurchase($order->chip_purchase_id, $order->reference);
        } catch (ChipException $e) {
            $order->forceFill(['last_checked_at' => now()])->save();

            if ($e->isNotFound()) {
                Log::warning('CHIP purchase not found', ['order' => $order->reference]);

                if ($order->status === PaymentOrder::STATUS_AWAITING_PAYMENT) {
                    $order->update([
                        'status' => PaymentOrder::STATUS_FAILED,
                        'failure_reason' => 'Purchase not found at CHIP',
                    ]);
                }

                return ReconcileOutcome::NotFound;
            }

            return ReconcileOutcome::Unreachable;
        }

        $status = is_string($purchase['status'] ?? null) ? strtolower(substr($purchase['status'], 0, 32)) : '';
        $previousStatus = $order->chip_status;

        $order->forceFill(['last_checked_at' => now(), 'chip_status' => $status ?: $order->chip_status])->save();

        if (! in_array($status, self::PAID, true)) {
            $closedLocally = in_array($order->status, [PaymentOrder::STATUS_FAILED, PaymentOrder::STATUS_CANCELLED, PaymentOrder::STATUS_EXPIRED], true);

            if ($closedLocally) {
                return ReconcileOutcome::NotApplicable;
            }

            if ($status === 'cancelled') {
                return $this->fulfilment->close($order, PaymentOrder::STATUS_CANCELLED, $status, $actor);
            }

            if ($status === 'expired') {
                return $this->fulfilment->close($order, PaymentOrder::STATUS_EXPIRED, $status, $actor);
            }

            if (in_array($status, self::REVIEW, true)) {
                return $this->fulfilment->markNeedsReview($order, ['CHIP reports the purchase as '.$status.' before fulfilment.'], $status);
            }

            if (in_array($status, self::FAILED_ATTEMPT, true) && $previousStatus !== $status) {
                PaymentLog::activity('Payment failed', [
                    'reference' => $order->reference,
                    'chip_status' => $status,
                    'source' => $source,
                ], $order, $actor ?: false);
            }

            return ReconcileOutcome::StillPending;
        }

        $problems = PurchaseVerifier::problems($order, $purchase, $settings);

        if ($problems !== []) {
            if (array_intersect(array_keys($problems), PurchaseVerifier::SUSPICIOUS) !== []) {
                SecurityPolicy::audit(
                    'payment',
                    'Suspicious payment: amount/currency/brand mismatch',
                    PurchaseVerifier::auditValues($order, $purchase, $settings) + ['problems' => array_keys($problems)],
                    null,
                    $order
                );
            }

            return $this->fulfilment->markNeedsReview($order, array_values($problems), $status);
        }

        return $this->fulfilment->fulfil($order, $purchase, $actor);
    }

    private function recordRecheck(PaymentOrder $order, ReconcileOutcome $outcome): void
    {
        try {
            PaymentEvent::create([
                'payment_order_id' => $order->id,
                'source' => 'recheck',
                'event_type' => 'recheck',
                'chip_purchase_id' => $order->chip_purchase_id,
                'signature_valid' => null,
                'result' => $outcome->eventResult(),
                'message' => Str::limit($outcome->adminMessage($order->chip_status), 490, ''),
                'payload' => null,
                'dedupe_key' => hash('sha256', 'recheck|'.$order->chip_purchase_id.'|'.Str::ulid()),
                'ip_address' => null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Re-check event not recorded', ['order' => $order->reference, 'error' => get_class($e)]);
        }
    }
}
