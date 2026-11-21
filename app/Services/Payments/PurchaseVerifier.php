<?php

namespace App\Services\Payments;

use App\Models\PaymentOrder;
use App\Models\PaymentSetting;

/**
 * Compares a paid purchase, as read from CHIP's GET /purchases/{id}/, with the
 * order it should settle. Any problem means the order goes to needs_review
 * instead of being fulfilled.
 */
class PurchaseVerifier
{
    /**
     * Problems that suggest tampering or a misconfiguration, worth a
     * "Suspicious payment" Security Audit entry.
     */
    public const SUSPICIOUS = ['id', 'amount', 'currency', 'brand', 'reference', 'mode'];

    /**
     * @param  array<string, mixed>  $purchase
     * @return array<string, string> problem key => reason
     */
    public static function problems(PaymentOrder $order, array $purchase, PaymentSetting $settings): array
    {
        $problems = [];
        $details = is_array($purchase['purchase'] ?? null) ? $purchase['purchase'] : [];

        if (($purchase['id'] ?? null) !== $order->chip_purchase_id) {
            $problems['id'] = 'Purchase id does not match the order.';
        }

        if (! isset($details['total']) || ! is_numeric($details['total']) || (int) $details['total'] !== (int) $order->amount_cents) {
            $problems['amount'] = 'Amount '.(is_scalar($details['total'] ?? null) ? $details['total'] : 'missing').' does not match '.$order->amount_cents.'.';
        }

        if (($details['currency'] ?? null) !== $order->currency) {
            $problems['currency'] = 'Currency '.(is_scalar($details['currency'] ?? null) ? $details['currency'] : 'missing').' does not match '.$order->currency.'.';
        }

        if (($purchase['brand_id'] ?? null) !== $settings->brand_id) {
            $problems['brand'] = 'Brand does not match the configured Brand ID.';
        }

        if (($purchase['reference'] ?? null) !== $order->reference) {
            $problems['reference'] = 'Reference does not match the order.';
        }

        if ((bool) ($purchase['is_test'] ?? false) !== ($settings->mode === 'test')) {
            $problems['mode'] = 'Purchase test flag does not match the configured mode ('.$settings->mode.').';
        }

        if (($purchase['marked_as_paid'] ?? false) === true) {
            $problems['marked_as_paid'] = 'Purchase was marked as paid manually in CHIP.';
        }

        return $problems;
    }

    /**
     * Expected and received values for the audit entry (no personal data).
     *
     * @param  array<string, mixed>  $purchase
     * @return array<string, mixed>
     */
    public static function auditValues(PaymentOrder $order, array $purchase, PaymentSetting $settings): array
    {
        $details = is_array($purchase['purchase'] ?? null) ? $purchase['purchase'] : [];

        return [
            'reference' => $order->reference,
            'chip_purchase_id' => $order->chip_purchase_id,
            'expected' => [
                'amount' => $order->amount_cents,
                'currency' => $order->currency,
                'brand_id' => $settings->brand_id,
                'is_test' => $settings->mode === 'test',
            ],
            'received' => [
                'id' => is_scalar($purchase['id'] ?? null) ? $purchase['id'] : null,
                'amount' => is_scalar($details['total'] ?? null) ? $details['total'] : null,
                'currency' => is_scalar($details['currency'] ?? null) ? $details['currency'] : null,
                'brand_id' => is_scalar($purchase['brand_id'] ?? null) ? $purchase['brand_id'] : null,
                'reference' => is_scalar($purchase['reference'] ?? null) ? $purchase['reference'] : null,
                'is_test' => (bool) ($purchase['is_test'] ?? false),
            ],
        ];
    }
}
