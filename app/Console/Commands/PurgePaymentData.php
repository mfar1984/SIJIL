<?php

namespace App\Console\Commands;

use App\Models\PaymentEvent;
use App\Models\PaymentOrder;
use Illuminate\Console\Command;

/**
 * Removes the buyer's personal data from unpaid orders after 30 days (the
 * order row stays with its non-personal fields), and the payloads of their
 * CHIP deliveries. Fulfilled and needs-review orders keep their snapshot.
 */
class PurgePaymentData extends Command
{
    protected $signature = 'payments:purge
        {--dry-run : Count what would be purged without changing anything}';

    protected $description = 'Purge buyer details of unpaid CHIP orders older than 30 days';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $cutoff = now()->subDays((int) config('payments.unpaid_purge_days', 30));

        $orders = PaymentOrder::whereIn('status', [
            PaymentOrder::STATUS_PENDING,
            PaymentOrder::STATUS_AWAITING_PAYMENT,
            PaymentOrder::STATUS_FAILED,
            PaymentOrder::STATUS_CANCELLED,
            PaymentOrder::STATUS_EXPIRED,
        ])->where('created_at', '<', $cutoff)->whereNull('purged_at');

        $orphans = PaymentEvent::whereNull('payment_order_id')->where('created_at', '<', $cutoff)->whereNotNull('payload');

        $orderCount = (clone $orders)->count();
        $orphanCount = (clone $orphans)->count();

        if ($dry) {
            $this->info("[dry-run] {$orderCount} order(s) and {$orphanCount} unlinked delivery payload(s) would be purged.");

            return self::SUCCESS;
        }

        $purged = 0;

        $orders->orderBy('id')->chunkById(200, function ($chunk) use (&$purged) {
            foreach ($chunk as $order) {
                $order->forceFill([
                    'buyer_snapshot' => null,
                    'email' => null,
                    'ip_hash' => null,
                    'checkout_url' => null,
                    'purged_at' => now(),
                ])->save();

                PaymentEvent::where('payment_order_id', $order->id)->update(['payload' => null]);
                $purged++;
            }
        });

        $cleared = $orphans->update(['payload' => null]);

        $this->info("Purged {$purged} order(s) and {$cleared} unlinked delivery payload(s).");

        return self::SUCCESS;
    }
}
