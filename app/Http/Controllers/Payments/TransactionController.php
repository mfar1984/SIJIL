<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PaymentOrder;
use App\Support\SystemSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * BILLING > Payment > Transactions: every checkout order.
 */
class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = PaymentOrder::query()->with('package');

        if (array_key_exists((string) $request->input('status'), PaymentOrder::STATUSES)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('package')) {
            $query->where('package_code', (string) $request->input('package'));
        }

        if ($from = $this->date($request->input('from'))) {
            $query->where('created_at', '>=', $from->startOfDay());
        }

        if ($to = $this->date($request->input('to'))) {
            $query->where('created_at', '<=', $to->endOfDay());
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('email', 'LIKE', '%'.addcslashes(strtolower($search), '%_\\').'%')
                    ->orWhere('reference', strtoupper($search))
                    ->orWhere('chip_purchase_id', strtolower($search));
            });
        }

        $orders = $query->orderByDesc('created_at')->orderByDesc('id')
            ->paginate(SystemSettings::perPage($request))
            ->withQueryString();

        return view('payments.transactions.index', [
            'orders' => $orders,
            'packages' => Package::orderBy('sort_order')->get(),
            'statuses' => PaymentOrder::STATUSES,
        ]);
    }

    public function show(PaymentOrder $order)
    {
        $order->load(['package', 'user', 'events' => fn ($q) => $q->orderBy('created_at')->orderBy('id')]);

        return view('payments.transactions.show', [
            'order' => $order,
            'snapshot' => $order->safeSnapshot(),
            'timeline' => $this->timeline($order),
        ]);
    }

    /**
     * Status timeline, oldest first.
     *
     * @return list<array{at: mixed, label: string, detail: ?string}>
     */
    private function timeline(PaymentOrder $order): array
    {
        $items = [['at' => $order->created_at, 'label' => 'Order created', 'detail' => $order->type === 'renewal' ? 'Renewal' : 'New account']];

        if ($order->chip_purchase_id) {
            $items[] = ['at' => $order->created_at, 'label' => 'CHIP purchase created', 'detail' => $order->chip_purchase_id];
        }

        foreach ($order->events as $event) {
            $items[] = [
                'at' => $event->created_at,
                'label' => ucfirst($event->source).($event->event_type ? ': '.$event->event_type : ''),
                'detail' => str_replace('_', ' ', $event->result).($event->message ? ' — '.$event->message : ''),
            ];
        }

        if ($order->paid_at) {
            $items[] = ['at' => $order->paid_at, 'label' => 'Paid', 'detail' => null];
        }

        if ($order->fulfilled_at) {
            $items[] = ['at' => $order->fulfilled_at, 'label' => 'Fulfilled', 'detail' => $order->new_expires_at ? 'Package expires '.SystemSettings::dateTime($order->new_expires_at) : null];
        }

        if (in_array($order->status, [PaymentOrder::STATUS_FAILED, PaymentOrder::STATUS_CANCELLED, PaymentOrder::STATUS_EXPIRED, PaymentOrder::STATUS_NEEDS_REVIEW], true)) {
            $items[] = [
                'at' => $order->updated_at,
                'label' => $order->statusLabel(),
                'detail' => $order->review_reason ?: $order->failure_reason,
            ];
        }

        usort($items, fn ($a, $b) => Carbon::parse($a['at'])->getTimestamp() <=> Carbon::parse($b['at'])->getTimestamp());

        return $items;
    }

    private function date(mixed $value): ?Carbon
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value, config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }
}
