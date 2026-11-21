<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\PaymentEvent;
use App\Support\SystemSettings;
use Illuminate\Http\Request;

/**
 * BILLING > Payment > Webhook Logs: every inbound CHIP delivery and every
 * server-side re-check.
 */
class WebhookLogController extends Controller
{
    public function index(Request $request)
    {
        $query = PaymentEvent::query()->with('order:id,reference,status');

        if (in_array($request->input('result'), PaymentEvent::RESULTS, true)) {
            $query->where('result', $request->input('result'));
        }

        match ((string) $request->input('signature')) {
            'valid' => $query->where('signature_valid', true),
            'invalid' => $query->where('signature_valid', false),
            default => null,
        };

        if (array_key_exists((string) $request->input('source'), PaymentEvent::SOURCES)) {
            $query->where('source', $request->input('source'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('chip_purchase_id', strtolower($search))
                    ->orWhereHas('order', fn ($o) => $o->where('reference', strtoupper($search)));
            });
        }

        $events = $query->orderByDesc('created_at')->orderByDesc('id')
            ->paginate(SystemSettings::perPage($request))
            ->withQueryString();

        return view('payments.webhook-logs.index', [
            'events' => $events,
            'results' => PaymentEvent::RESULTS,
            'sources' => PaymentEvent::SOURCES,
        ]);
    }

    public function show(PaymentEvent $event)
    {
        $event->load('order');

        $raw = $event->safePayload();
        $pretty = null;

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            $pretty = json_last_error() === JSON_ERROR_NONE
                ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                : $raw;
        }

        return view('payments.webhook-logs.show', [
            'event' => $event,
            'payload' => $pretty,
        ]);
    }
}
