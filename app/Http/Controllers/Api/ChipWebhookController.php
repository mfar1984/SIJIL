<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentEvent;
use App\Models\PaymentOrder;
use App\Models\PaymentSetting;
use App\Services\Payments\ChipClient;
use App\Services\Payments\ChipException;
use App\Services\Payments\ChipSignature;
use App\Services\Payments\PaymentReconciler;
use App\Services\Payments\ReconcileOutcome;
use App\Support\PaymentLog;
use App\Support\SecurityPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Inbound CHIP deliveries.
 *
 *  - POST api/payments/chip/callback: the per-purchase success_callback,
 *    signed with the company key from GET /public_key/.
 *  - POST api/payments/chip/webhook: the portal webhook, signed with the key
 *    the administrator pasted on the Payment tab.
 *
 * A delivery never fulfils anything by itself. A valid signature only triggers
 * PaymentReconciler, which asks CHIP for the purchase and acts on that answer.
 * Every delivery is stored in payment_events (Webhook Logs) before acting.
 */
class ChipWebhookController extends Controller
{
    private const MAX_BODY = 262144;

    public function __construct(private PaymentReconciler $reconciler)
    {
    }

    public function callback(Request $request): JsonResponse
    {
        return $this->handle($request, 'callback');
    }

    public function webhook(Request $request): JsonResponse
    {
        return $this->handle($request, 'webhook');
    }

    private function handle(Request $request, string $source): JsonResponse
    {
        $raw = (string) $request->getContent();

        if (strlen($raw) > self::MAX_BODY) {
            $this->record($source, null, null, false, 'rejected', 'Body too large', null, $request);

            return $this->answer('rejected', 413);
        }

        $settings = PaymentSetting::current();

        if (! $settings->isConfigured()) {
            $this->record($source, null, null, null, 'error', 'CHIP credentials are not configured', $raw, $request);

            return $this->answer('error', 503);
        }

        // Signature key.
        if ($source === 'callback') {
            $pem = $settings->company_public_key;

            if (! filled($pem)) {
                $pem = $this->refreshCompanyKey($settings);

                if ($pem === null) {
                    $this->record($source, null, null, null, 'error', 'Could not fetch the CHIP public key', $raw, $request);

                    return $this->answer('error', 503);
                }
            }
        } else {
            $pem = $settings->webhook_public_key;
        }

        $signature = $request->header('X-Signature');
        $valid = ChipSignature::verify($raw, $signature, $pem);

        // Key rotation: re-fetch the company key once if the stored one is stale.
        if (! $valid && $source === 'callback' && filled($signature)
            && (! $settings->company_public_key_fetched_at || $settings->company_public_key_fetched_at->lt(now()->subHour()))) {
            $fresh = $this->refreshCompanyKey($settings);

            if ($fresh !== null && $fresh !== $pem) {
                $valid = ChipSignature::verify($raw, $signature, $fresh);
            }
        }

        $payload = json_decode($raw, true);
        $payload = is_array($payload) ? $payload : null;
        $eventType = is_string($payload['event_type'] ?? null) ? Str::limit($payload['event_type'], 64, '') : null;
        $purchaseId = $this->purchaseId($payload, $eventType);

        if (! $valid) {
            $message = ($source === 'webhook' && ! filled($pem)) ? 'No webhook public key configured' : 'Signature verification failed';
            // Keep only the first 2 KB of an unverified body: enough to diagnose a
            // misconfigured key, too little for junk requests to fill the database.
            $this->record($source, $eventType, $purchaseId, false, 'rejected', $message, substr($raw, 0, 2048), $request);

            SecurityPolicy::audit('payment', 'Payment webhook signature verification failed', array_filter([
                'source' => $source,
                'purchase_id' => $purchaseId,
                'ip_address' => $request->ip(),
                'reason' => $message,
            ]));

            return $this->answer('invalid_signature', 401);
        }

        if ($payload === null) {
            $this->record($source, null, null, true, 'ignored', 'Body is not a JSON object', $raw, $request);

            return $this->answer('ok', 200);
        }

        $dedupe = hash('sha256', $source.'|'.$purchaseId.'|'.$eventType.'|'.hash('sha256', $raw));
        $event = $this->record($source, $eventType, $purchaseId, true, 'error', 'Processing', $raw, $request, $dedupe);

        $first = PaymentEvent::where('dedupe_key', $dedupe)
            ->where('id', '!=', $event?->id ?? 0)
            ->where('signature_valid', true)
            ->whereIn('result', PaymentEvent::FINAL_RESULTS)
            ->orderBy('id')
            ->first();

        if ($first) {
            $this->finish($event, 'replayed', 'Duplicate of event #'.$first->id, $first->payment_order_id);

            SecurityPolicy::audit('payment', 'Duplicate payment webhook ignored', [
                'source' => $source,
                'purchase_id' => $purchaseId,
                'event_type' => $eventType,
                'first_event_id' => $first->id,
            ]);

            return $this->answer('ok', 200);
        }

        $order = $purchaseId ? PaymentOrder::where('chip_purchase_id', $purchaseId)->first() : null;

        if (! $order) {
            $reference = is_string($payload['reference'] ?? null) ? $payload['reference'] : null;
            $saving = $reference && PaymentOrder::where('reference', $reference)->whereNull('chip_purchase_id')
                ->whereIn('status', [PaymentOrder::STATUS_PENDING, PaymentOrder::STATUS_AWAITING_PAYMENT])->exists();

            if ($saving) {
                $this->finish($event, 'order_not_ready', 'The order is still saving its purchase id');

                return $this->answer('order_not_ready', 503);
            }

            $this->finish($event, 'ignored', 'Unknown purchase');

            return $this->answer('ok', 200);
        }

        try {
            return $this->act($source, $eventType, $order, $event);
        } catch (\Throwable $e) {
            Log::error('CHIP delivery processing failed', ['order' => $order->reference, 'error' => get_class($e), 'message' => $e->getMessage()]);
            report($e);
            $this->finish($event, 'error', 'Processing failed ('.class_basename($e).')', $order->id);

            return $this->answer('error', 500);
        }
    }

    private function act(string $source, ?string $eventType, PaymentOrder $order, ?PaymentEvent $event): JsonResponse
    {
        // CHIP sends success callbacks only for paid purchases, but the event
        // type is not trusted to choose the action: every callback reconciles.
        if ($source === 'callback' || $eventType === 'purchase.paid') {
            return $this->fromOutcome($this->reconciler->reconcile($order, $source, $event), $order, $event);
        }

        switch ($eventType) {
            case 'purchase.cancelled':
                $outcome = $this->reconciler->reconcile($order, $source, $event);
                $order->refresh();

                if ($outcome === ReconcileOutcome::StillPending) {
                    $this->finish($event, 'recorded', 'CHIP does not report the purchase as cancelled (status: '.($order->chip_status ?? 'unknown').')', $order->id);

                    return $this->answer('ok', 200);
                }

                return $this->fromOutcome($outcome, $order, $event);

            case 'purchase.payment_failure':
                $order->forceFill(['chip_status' => 'error'])->save();
                PaymentLog::activity('Payment failed', ['reference' => $order->reference, 'source' => $source], $order, false);
                $this->finish($event, 'recorded', 'Payment attempt failed', $order->id);

                return $this->answer('ok', 200);

            case 'payment.refunded':
            case 'purchase.pending_refund':
            case 'payment.charged_back':
                $this->finish($event, 'recorded', 'Recorded ('.$eventType.')', $order->id);

                return $this->answer('ok', 200);

            default:
                $this->finish($event, 'ignored', 'Event type not handled', $order->id);

                return $this->answer('ok', 200);
        }
    }

    private function fromOutcome(ReconcileOutcome $outcome, PaymentOrder $order, ?PaymentEvent $event): JsonResponse
    {
        $order->refresh();
        $result = $outcome->eventResult();

        $message = match ($outcome) {
            ReconcileOutcome::NotFound => 'Purchase not found at CHIP',
            ReconcileOutcome::NotApplicable => 'Order is '.$order->status,
            ReconcileOutcome::StillPending => 'CHIP does not report the purchase as paid yet (status: '.($order->chip_status ?? 'unknown').')',
            ReconcileOutcome::Unreachable => 'CHIP could not be reached',
            ReconcileOutcome::NeedsReview => $order->review_reason ?: 'Needs review',
            default => $outcome->adminMessage($order->chip_status),
        };

        $this->finish($event, $result, $message, $order->id);

        $http = in_array($outcome, [ReconcileOutcome::StillPending, ReconcileOutcome::Unreachable], true) ? 503 : 200;

        return $this->answer($http === 200 ? 'ok' : $result, $http);
    }

    /**
     * payload id for purchase.* events (and callbacks, which carry the
     * purchase itself), related_to.id for payment.* events.
     *
     * @param  array<string, mixed>|null  $payload
     */
    private function purchaseId(?array $payload, ?string $eventType): ?string
    {
        if (! $payload) {
            return null;
        }

        $id = ($eventType !== null && str_starts_with($eventType, 'payment.'))
            ? ($payload['related_to']['id'] ?? null)
            : ($payload['id'] ?? null);

        return is_string($id) && preg_match('/^[0-9a-f-]{36}$/i', $id) ? strtolower($id) : null;
    }

    private function refreshCompanyKey(PaymentSetting $settings): ?string
    {
        try {
            $pem = ChipClient::fromSettings($settings)->publicKey();
        } catch (ChipException) {
            return null;
        }

        $settings->forceFill(['company_public_key' => $pem, 'company_public_key_fetched_at' => now()])->save();

        return $pem;
    }

    private function record(string $source, ?string $eventType, ?string $purchaseId, ?bool $signatureValid, string $result, string $message, ?string $raw, Request $request, ?string $dedupe = null): ?PaymentEvent
    {
        try {
            return PaymentEvent::create([
                'payment_order_id' => null,
                'source' => $source,
                'event_type' => $eventType,
                'chip_purchase_id' => $purchaseId,
                'signature_valid' => $signatureValid,
                'result' => $result,
                'message' => Str::limit($message, 490, ''),
                'payload' => $raw,
                'dedupe_key' => $dedupe ?? hash('sha256', $source.'|'.$purchaseId.'|'.$eventType.'|'.hash('sha256', (string) $raw).'|'.Str::ulid()),
                'ip_address' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('CHIP delivery not recorded', ['source' => $source, 'error' => get_class($e)]);

            return null;
        }
    }

    private function finish(?PaymentEvent $event, string $result, string $message, ?int $orderId = null): void
    {
        if (! $event) {
            return;
        }

        try {
            $event->forceFill(array_filter([
                'result' => $result,
                'message' => Str::limit($message, 490, ''),
                'payment_order_id' => $orderId,
            ], fn ($v) => $v !== null))->save();
        } catch (\Throwable $e) {
            Log::warning('CHIP delivery result not saved', ['event' => $event->id, 'error' => get_class($e)]);
        }
    }

    private function answer(string $status, int $http): JsonResponse
    {
        return response()->json(['status' => $status], $http, ['Cache-Control' => 'no-store']);
    }
}
