<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Billing</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Payment</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Webhook Logs</span>
    </x-slot>

    <x-slot name="title">Webhook Logs</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons-outlined mr-2 text-primary-DEFAULT">webhook</span>
                <h1 class="text-xl font-bold text-gray-800">Webhook Logs</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">Every callback and webhook received from CHIP, and every status re-check made with CHIP</p>
        </div>

        <div class="p-4">
            @php $filtered = collect(request()->only(['search', 'result', 'signature', 'source']))->filter()->isNotEmpty(); @endphp
            <form method="GET" action="{{ route('payments.webhook-logs.index') }}" class="flex flex-wrap items-center gap-2 mb-4">
                <input type="text" name="search" value="{{ request('search') }}" maxlength="64" aria-label="Search"
                       placeholder="CHIP purchase id or order reference..."
                       class="flex-1 min-w-[12rem] h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">

                <select name="source" onchange="this.form.submit()" aria-label="Source"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[9rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Sources</option>
                    @foreach($sources as $value => $label)
                        <option value="{{ $value }}" @selected(request('source') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="signature" onchange="this.form.submit()" aria-label="Signature"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[10rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">Any Signature</option>
                    <option value="valid" @selected(request('signature') === 'valid')>Valid</option>
                    <option value="invalid" @selected(request('signature') === 'invalid')>Invalid</option>
                </select>

                <select name="result" onchange="this.form.submit()" aria-label="Result"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[11rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Results</option>
                    @foreach($results as $value)
                        <option value="{{ $value }}" @selected(request('result') === $value)>{{ ucfirst(str_replace('_', ' ', $value)) }}</option>
                    @endforeach
                </select>

                <button type="submit" title="Search"
                        class="h-9 px-3 bg-primary-DEFAULT hover:bg-primary-dark text-white rounded text-xs flex items-center shrink-0">
                    <span class="material-icons-outlined text-xs">search</span>
                </button>
                @if($filtered)
                    <a href="{{ route('payments.webhook-logs.index') }}" class="text-xs text-gray-500 underline shrink-0">Reset</a>
                @endif
            </form>

            <div class="overflow-x-auto border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left">Time</th>
                            <th class="py-3 px-4 text-left">Source</th>
                            <th class="py-3 px-4 text-left">Event</th>
                            <th class="py-3 px-4 text-left">Purchase id</th>
                            <th class="py-3 px-4 text-left">Signature</th>
                            <th class="py-3 px-4 text-left">Result</th>
                            <th class="py-3 px-4 text-left">Message</th>
                            <th class="py-3 px-4 text-left">Order</th>
                            <th class="py-3 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($events as $event)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 whitespace-nowrap">{{ \App\Support\SystemSettings::dateTime($event->created_at) }}</td>
                                <td class="py-3 px-4">{{ $sources[$event->source] ?? $event->source }}</td>
                                <td class="py-3 px-4 font-mono">{{ $event->event_type ?? '—' }}</td>
                                <td class="py-3 px-4 font-mono break-all">{{ $event->chip_purchase_id ?? '—' }}</td>
                                <td class="py-3 px-4">
                                    @if($event->signature_valid === null)
                                        <span class="text-gray-400">n/a</span>
                                    @elseif($event->signature_valid)
                                        @include('payments.partials.badge', ['tone' => 'green', 'label' => 'valid'])
                                    @else
                                        @include('payments.partials.badge', ['tone' => 'red', 'label' => 'invalid'])
                                    @endif
                                </td>
                                <td class="py-3 px-4">@include('payments.partials.event-result', ['result' => $event->result])</td>
                                <td class="py-3 px-4 max-w-[18rem] truncate" title="{{ $event->message }}">{{ $event->message ?? '' }}</td>
                                <td class="py-3 px-4 font-mono whitespace-nowrap">
                                    @if($event->order)
                                        @can('payment_transactions.read')
                                            <a href="{{ route('payments.transactions.show', $event->order) }}" class="text-primary-DEFAULT hover:underline">{{ $event->order->reference }}</a>
                                        @else
                                            {{ $event->order->reference }}
                                        @endcan
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <a href="{{ route('payments.webhook-logs.show', $event->id) }}" class="inline-block p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View">
                                        <span class="material-icons-outlined text-primary-DEFAULT text-xs">visibility</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-6 px-4 text-center text-xs text-gray-500">
                                    {{ $filtered ? 'No deliveries match these filters.' : 'Nothing received from CHIP yet.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $events->links('components.pagination-modern') }}
            </div>
        </div>
    </div>
</x-app-layout>
