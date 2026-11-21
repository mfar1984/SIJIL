<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Billing</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Payment</span>
        <span class="mx-2 text-gray-500">/</span>
        <a href="{{ route('payments.webhook-logs.index') }}" class="hover:underline">Webhook Logs</a>
        <span class="mx-2 text-gray-500">/</span>
        <span>#{{ $event->id }}</span>
    </x-slot>

    <x-slot name="title">Webhook Log #{{ $event->id }}</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-wrap justify-between items-start gap-3">
                <div class="flex items-center gap-2">
                    <span class="material-icons-outlined text-primary-DEFAULT">webhook</span>
                    <h1 class="text-xl font-bold text-gray-800">Delivery #{{ $event->id }}</h1>
                    @include('payments.partials.event-result', ['result' => $event->result])
                </div>
                <a href="{{ route('payments.webhook-logs.index') }}" class="px-3 h-[36px] border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50 inline-flex items-center">
                    <span class="material-icons-outlined text-xs mr-1">arrow_back</span>
                    Back to Webhook Logs
                </a>
            </div>
        </div>

        <div class="p-4 space-y-4">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2 text-xs border border-gray-200 rounded-md p-4">
                <div class="flex gap-3"><dt class="text-gray-500 w-36 shrink-0">Received</dt><dd>{{ \App\Support\SystemSettings::dateTime($event->created_at) }}</dd></div>
                <div class="flex gap-3"><dt class="text-gray-500 w-36 shrink-0">Source</dt><dd>{{ \App\Models\PaymentEvent::SOURCES[$event->source] ?? $event->source }}</dd></div>
                <div class="flex gap-3"><dt class="text-gray-500 w-36 shrink-0">Event type</dt><dd class="font-mono">{{ $event->event_type ?? '—' }}</dd></div>
                <div class="flex gap-3"><dt class="text-gray-500 w-36 shrink-0">CHIP purchase id</dt><dd class="font-mono break-all">{{ $event->chip_purchase_id ?? '—' }}</dd></div>
                <div class="flex gap-3"><dt class="text-gray-500 w-36 shrink-0">Signature</dt>
                    <dd>{{ $event->signature_valid === null ? 'Not applicable (re-check)' : ($event->signature_valid ? 'Valid' : 'Invalid') }}</dd></div>
                <div class="flex gap-3"><dt class="text-gray-500 w-36 shrink-0">Sender IP</dt><dd class="font-mono">{{ $event->ip_address ?? '—' }}</dd></div>
                <div class="flex gap-3"><dt class="text-gray-500 w-36 shrink-0">Order</dt>
                    <dd class="font-mono">
                        @if($event->order)
                            @can('payment_transactions.read')
                                <a href="{{ route('payments.transactions.show', $event->order) }}" class="text-primary-DEFAULT hover:underline">{{ $event->order->reference }}</a>
                            @else
                                {{ $event->order->reference }}
                            @endcan
                        @else
                            —
                        @endif
                    </dd></div>
                <div class="flex gap-3"><dt class="text-gray-500 w-36 shrink-0">Message</dt><dd class="break-all">{{ $event->message ?? '—' }}</dd></div>
                <div class="flex gap-3 md:col-span-2"><dt class="text-gray-500 w-36 shrink-0">Dedupe key</dt><dd class="font-mono break-all text-gray-600">{{ $event->dedupe_key }}</dd></div>
            </dl>

            <div class="border border-gray-200 rounded-md">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">data_object</span>
                        Payload
                    </h2>
                </div>
                <div class="p-4">
                    @if($payload !== null)
                        <pre class="bg-gray-50 border border-gray-200 rounded p-3 text-[11px] font-mono text-gray-800 overflow-x-auto max-h-[36rem]">{{ $payload }}</pre>
                    @elseif($event->getRawOriginal('payload'))
                        <p class="text-xs text-gray-500">The payload cannot be decrypted with the current application key.</p>
                    @else
                        <p class="text-xs text-gray-500">Payload purged or not stored.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
