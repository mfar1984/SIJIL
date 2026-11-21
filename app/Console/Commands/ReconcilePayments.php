<?php

namespace App\Console\Commands;

use App\Models\PaymentOrder;
use App\Services\Payments\OrderFulfilment;
use App\Services\Payments\PaymentReconciler;
use App\Services\Payments\ReconcileOutcome;
use Illuminate\Console\Command;

/**
 * Re-checks unpaid CHIP orders server-side, so a missed or never-delivered
 * webhook still leads to fulfilment, and expired purchases get closed.
 *
 * Runs in two passes. Orders whose deadline (due_at) has passed are settled
 * first: CHIP is asked once, and the order is expired only when CHIP answers
 * that it is still unpaid. The re-check pass then skips those orders, so CHIP
 * is never asked twice for the same order in one run.
 */
class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile
        {--limit=25 : Orders to check per run}
        {--min-age=10 : Minutes an order must be old, and since its last check}
        {--expire-limit=50 : Past-deadline orders to settle per run}';

    protected $description = 'Re-check awaiting CHIP payments with CHIP and fulfil the paid ones';

    public function handle(PaymentReconciler $reconciler, OrderFulfilment $fulfilment): int
    {
        $this->expirePass($reconciler, $fulfilment);

        $limit = max(1, min(200, (int) $this->option('limit')));
        $minAge = max(0, (int) $this->option('min-age'));
        $cutoff = now()->subMinutes($minAge);

        $orders = PaymentOrder::where('status', PaymentOrder::STATUS_AWAITING_PAYMENT)
            ->whereNotNull('chip_purchase_id')
            ->where('created_at', '>=', now()->subDays(7))
            ->where('created_at', '<=', $cutoff)
            ->where(fn ($q) => $q->whereNull('last_checked_at')->orWhere('last_checked_at', '<=', $cutoff))
            ->where(fn ($q) => $q->whereNull('due_at')->orWhere('due_at', '>', now()))
            ->orderByRaw('last_checked_at IS NOT NULL, last_checked_at ASC')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No awaiting payments to re-check.');

            return self::SUCCESS;
        }

        $counts = [];

        foreach ($orders as $order) {
            try {
                $outcome = $reconciler->reconcile($order, 'recheck');
                $counts[$outcome->value] = ($counts[$outcome->value] ?? 0) + 1;
                $this->line($order->reference.': '.$outcome->value);
            } catch (\Throwable $e) {
                $counts['error'] = ($counts['error'] ?? 0) + 1;
                $this->warn($order->reference.': error ('.class_basename($e).')');
                report($e);
            }
        }

        $this->info('Checked '.$orders->count().' order(s): '.collect($counts)->map(fn ($n, $k) => "$k=$n")->implode(', '));

        return self::SUCCESS;
    }

    /**
     * Settles orders whose deadline has passed: CHIP decides first, so a buyer
     * who paid at the last minute is fulfilled instead of expired. CHIP being
     * unreachable, or not knowing the purchase, leaves the order for the next
     * run; an order is never expired on an unknown CHIP state.
     */
    private function expirePass(PaymentReconciler $reconciler, OrderFulfilment $fulfilment): void
    {
        $limit = max(1, min(500, (int) $this->option('expire-limit')));

        $orders = PaymentOrder::whereIn('status', [PaymentOrder::STATUS_PENDING, PaymentOrder::STATUS_AWAITING_PAYMENT])
            ->whereNotNull('due_at')
            ->where('due_at', '<=', now())
            ->orderBy('due_at')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No past-deadline orders to settle.');

            return;
        }

        $counts = [];
        $record = function (string $key) use (&$counts) {
            $counts[$key] = ($counts[$key] ?? 0) + 1;

            return $key;
        };

        foreach ($orders as $order) {
            try {
                if (! $order->chip_purchase_id) {
                    $outcome = $fulfilment->close($order, PaymentOrder::STATUS_EXPIRED, $order->chip_status, false);
                    $this->line($order->reference.': past deadline, never reached CHIP -> '.$record($outcome->value));

                    continue;
                }

                $outcome = $reconciler->reconcile($order, 'recheck');
                $order->refresh();

                if (in_array($outcome, [ReconcileOutcome::Unreachable, ReconcileOutcome::NotFound], true)) {
                    $this->warn($order->reference.': past deadline, left for the next run ('.$record($outcome->value).')');

                    continue;
                }

                if ($outcome !== ReconcileOutcome::StillPending) {
                    $this->line($order->reference.': past deadline, CHIP settled it -> '.$record($outcome->value));

                    continue;
                }

                $closed = $fulfilment->close($order, PaymentOrder::STATUS_EXPIRED, $order->chip_status, false);
                $this->line($order->reference.': past deadline, still unpaid -> '.$record($closed->value));
            } catch (\Throwable $e) {
                $record('error');
                $this->warn($order->reference.': error ('.class_basename($e).')');
                report($e);
            }
        }

        $this->info('Settled '.$orders->count().' past-deadline order(s): '.collect($counts)->map(fn ($n, $k) => "$k=$n")->implode(', '));
    }
}
