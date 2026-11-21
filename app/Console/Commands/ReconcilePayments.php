<?php

namespace App\Console\Commands;

use App\Models\PaymentOrder;
use App\Services\Payments\PaymentReconciler;
use Illuminate\Console\Command;

/**
 * Re-checks unpaid CHIP orders server-side, so a missed or never-delivered
 * webhook still leads to fulfilment, and expired purchases get closed.
 */
class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile
        {--limit=25 : Orders to check per run}
        {--min-age=10 : Minutes an order must be old, and since its last check}';

    protected $description = 'Re-check awaiting CHIP payments with CHIP and fulfil the paid ones';

    public function handle(PaymentReconciler $reconciler): int
    {
        $limit = max(1, min(200, (int) $this->option('limit')));
        $minAge = max(0, (int) $this->option('min-age'));
        $cutoff = now()->subMinutes($minAge);

        $orders = PaymentOrder::where('status', PaymentOrder::STATUS_AWAITING_PAYMENT)
            ->whereNotNull('chip_purchase_id')
            ->where('created_at', '>=', now()->subDays(7))
            ->where('created_at', '<=', $cutoff)
            ->where(fn ($q) => $q->whereNull('last_checked_at')->orWhere('last_checked_at', '<=', $cutoff))
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
}
