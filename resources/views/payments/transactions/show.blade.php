<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Billing</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Payment</span>
        <span class="mx-2 text-gray-500">/</span>
        <a href="{{ route('payments.transactions.index') }}" class="hover:underline">Transactions</a>
        <span class="mx-2 text-gray-500">/</span>
        <span>{{ $order->reference }}</span>
    </x-slot>

    <x-slot name="title">Transaction {{ $order->reference }}</x-slot>

    @php
        $fmt = fn ($value) => \App\Support\SystemSettings::dateTime($value);
        $snapshotLabels = [
            'name' => 'Full name', 'email' => 'Email', 'phone' => 'Phone',
            'address_line1' => 'Address line 1', 'address_line2' => 'Address line 2', 'postcode' => 'Postcode',
            'city' => 'City', 'state' => 'State', 'country' => 'Country',
            'org_type' => 'Organization type', 'org_name' => 'Organization name',
            'org_address_line1' => 'Org address line 1', 'org_address_line2' => 'Org address line 2',
            'org_state' => 'Org state', 'org_city' => 'Org city', 'org_postcode' => 'Org postcode', 'org_country' => 'Org country',
            'org_telephone' => 'Org telephone', 'org_fax' => 'Org fax', 'org_email' => 'Org email', 'org_website' => 'Org website',
        ];
    @endphp

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-wrap justify-between items-start gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="material-icons-outlined text-primary-DEFAULT">receipt_long</span>
                        <h1 class="text-xl font-bold text-gray-800 font-mono">{{ $order->reference }}</h1>
                        @include('payments.partials.order-status', ['order' => $order])
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">{{ $order->package_name }} · {{ $order->amountDisplay() }} · {{ $order->type === 'renewal' ? 'Renewal' : 'New account' }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @can('payment_transactions.update')
                        @if($order->chip_purchase_id && ! in_array($order->status, ['fulfilled', 'needs_review'], true))
                            <form method="POST" action="{{ route('payments.transactions.recheck', $order) }}">
                                @csrf
                                <button type="submit" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 h-[36px] rounded shadow-sm text-xs font-medium inline-flex items-center">
                                    <span class="material-icons-outlined text-xs mr-1">sync</span>
                                    Re-check with CHIP
                                </button>
                            </form>
                        @endif
                        @if($order->status === 'fulfilled' && $order->type === 'new' && $order->user && ! $order->user->trashed() && ! $order->user->password_changed_at)
                            <form method="POST" action="{{ route('payments.transactions.resend', $order) }}">
                                @csrf
                                <button type="submit" class="px-3 h-[36px] border border-blue-300 text-blue-700 rounded text-xs font-medium hover:bg-blue-50 inline-flex items-center">
                                    <span class="material-icons-outlined text-xs mr-1">forward_to_inbox</span>
                                    Resend set-password link
                                </button>
                            </form>
                        @endif
                    @endcan
                    <a href="{{ route('payments.transactions.index') }}" class="px-3 h-[36px] border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50 inline-flex items-center">
                        <span class="material-icons-outlined text-xs mr-1">arrow_back</span>
                        Back to Transactions
                    </a>
                </div>
            </div>
        </div>

        <div class="p-4 space-y-4">
            @if (session('success'))
                <div class="p-3 bg-green-50 border border-green-200 text-green-700 rounded text-xs">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded text-xs">{{ session('error') }}</div>
            @endif
            @if($order->review_reason)
                <div class="p-3 bg-red-50 border border-red-200 text-red-800 rounded text-xs">
                    <span class="font-medium">Needs review:</span> {{ $order->review_reason }}
                </div>
            @endif
            @if($order->failure_reason)
                <div class="p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded text-xs">
                    <span class="font-medium">Failure:</span> {{ $order->failure_reason }}
                </div>
            @endif
            @if($order->status === 'fulfilled' && $order->type === 'new' && ! $order->set_password_sent_at)
                <div class="p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded text-xs">
                    Set-password email not sent. Check Settings &gt; Delivery for an active email configuration.
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                {{-- Payment --}}
                <div class="border border-gray-200 rounded-md">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">payments</span>
                            Payment
                        </h2>
                    </div>
                    <dl class="p-4 grid grid-cols-3 gap-x-3 gap-y-2 text-xs">
                        <dt class="text-gray-500">Package</dt>
                        <dd class="col-span-2">{{ $order->package_name }} <span class="text-gray-400 font-mono">({{ $order->package_code }})</span></dd>
                        <dt class="text-gray-500">Amount</dt>
                        <dd class="col-span-2">{{ $order->amountDisplay() }} {{ $order->currency }}</dd>
                        <dt class="text-gray-500">Duration</dt>
                        <dd class="col-span-2">{{ $order->duration_months }} {{ \Illuminate\Support\Str::plural('month', $order->duration_months) }}</dd>
                        <dt class="text-gray-500">CHIP purchase id</dt>
                        <dd class="col-span-2 font-mono break-all">{{ $order->chip_purchase_id ?? '—' }}</dd>
                        <dt class="text-gray-500">CHIP status</dt>
                        <dd class="col-span-2">{{ $order->chip_status ?? '—' }}</dd>
                        <dt class="text-gray-500">Deadline</dt>
                        <dd class="col-span-2">{{ $fmt($order->due_at) }}</dd>
                        <dt class="text-gray-500">Mode</dt>
                        <dd class="col-span-2">{{ $order->is_test === null ? '—' : ($order->is_test ? 'Test' : 'Live') }}</dd>
                        <dt class="text-gray-500">Paid</dt>
                        <dd class="col-span-2">{{ $fmt($order->paid_at) }}</dd>
                        <dt class="text-gray-500">Last checked</dt>
                        <dd class="col-span-2">{{ $fmt($order->last_checked_at) }}</dd>
                        <dt class="text-gray-500">Consent given</dt>
                        <dd class="col-span-2">{{ $fmt($order->consent_at) }}</dd>
                    </dl>
                </div>

                {{-- Account --}}
                <div class="border border-gray-200 rounded-md">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">manage_accounts</span>
                            Account
                        </h2>
                    </div>
                    <dl class="p-4 grid grid-cols-3 gap-x-3 gap-y-2 text-xs">
                        <dt class="text-gray-500">Linked user</dt>
                        <dd class="col-span-2">
                            @if($order->user)
                                @can('users.read')
                                    <a href="{{ route('user.show', $order->user->id) }}" class="text-primary-DEFAULT hover:underline">{{ $order->user->name }}</a>
                                @else
                                    {{ $order->user->name }}
                                @endcan
                                @if($order->user->trashed())
                                    <span class="text-gray-400">(deleted)</span>
                                @endif
                            @else
                                —
                            @endif
                        </dd>
                        <dt class="text-gray-500">Previous expiry</dt>
                        <dd class="col-span-2">{{ $fmt($order->previous_expires_at) }}</dd>
                        <dt class="text-gray-500">Package start</dt>
                        <dd class="col-span-2">{{ $fmt($order->package_started_at) }}</dd>
                        <dt class="text-gray-500">New expiry</dt>
                        <dd class="col-span-2">{{ $fmt($order->new_expires_at) }}</dd>
                        <dt class="text-gray-500">Set-password email</dt>
                        <dd class="col-span-2">{{ $order->set_password_sent_at ? 'Sent '.$fmt($order->set_password_sent_at) : ($order->type === 'new' ? 'Not sent' : 'Not needed (renewal)') }}</dd>
                        <dt class="text-gray-500">Fulfilled</dt>
                        <dd class="col-span-2">{{ $fmt($order->fulfilled_at) }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Buyer snapshot --}}
            <div class="border border-gray-200 rounded-md">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">badge</span>
                        Buyer details at checkout
                    </h2>
                </div>
                <div class="p-4">
                    @if($order->purged_at)
                        <p class="text-xs text-gray-500">Purged on {{ $fmt($order->purged_at) }}. Buyer details of unpaid orders are removed after {{ config('payments.unpaid_purge_days') }} days.</p>
                    @elseif(! is_array($snapshot))
                        <p class="text-xs text-gray-500">No buyer details stored{{ $order->getRawOriginal('buyer_snapshot') ? ' (they cannot be decrypted with the current application key)' : '' }}.</p>
                    @else
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2 text-xs">
                            @foreach($snapshotLabels as $key => $label)
                                @if(filled($snapshot[$key] ?? null))
                                    <div class="flex gap-3">
                                        <dt class="text-gray-500 w-40 shrink-0">{{ $label }}</dt>
                                        <dd class="break-all">{{ $snapshot[$key] }}</dd>
                                    </div>
                                @endif
                            @endforeach
                        </dl>
                    @endif
                </div>
            </div>

            {{-- Timeline --}}
            <div class="border border-gray-200 rounded-md">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">timeline</span>
                        Status timeline
                    </h2>
                </div>
                <ol class="p-4 space-y-2">
                    @foreach($timeline as $item)
                        <li class="flex gap-3 text-xs">
                            <span class="text-gray-500 w-36 shrink-0 whitespace-nowrap">{{ $fmt($item['at']) }}</span>
                            <span class="font-medium text-gray-800">{{ $item['label'] }}</span>
                            @if($item['detail'])
                                <span class="text-gray-600 break-all">{{ $item['detail'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>

            {{-- Events --}}
            <div class="border border-gray-200 rounded-md">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">webhook</span>
                        CHIP deliveries and re-checks
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-[11px] uppercase border-b border-gray-200">
                                <th class="py-2 px-4 text-left">Time</th>
                                <th class="py-2 px-4 text-left">Source</th>
                                <th class="py-2 px-4 text-left">Event</th>
                                <th class="py-2 px-4 text-left">Signature</th>
                                <th class="py-2 px-4 text-left">Result</th>
                                <th class="py-2 px-4 text-center">Log</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($order->events as $event)
                                <tr class="text-xs">
                                    <td class="py-2 px-4 whitespace-nowrap">{{ $fmt($event->created_at) }}</td>
                                    <td class="py-2 px-4">{{ \App\Models\PaymentEvent::SOURCES[$event->source] ?? $event->source }}</td>
                                    <td class="py-2 px-4 font-mono">{{ $event->event_type ?? '—' }}</td>
                                    <td class="py-2 px-4">
                                        @if($event->signature_valid === null)
                                            <span class="text-gray-400">n/a</span>
                                        @elseif($event->signature_valid)
                                            @include('payments.partials.badge', ['tone' => 'green', 'label' => 'valid'])
                                        @else
                                            @include('payments.partials.badge', ['tone' => 'red', 'label' => 'invalid'])
                                        @endif
                                    </td>
                                    <td class="py-2 px-4">@include('payments.partials.event-result', ['result' => $event->result])</td>
                                    <td class="py-2 px-4 text-center">
                                        @can('payment_webhook_logs.read')
                                            <a href="{{ route('payments.webhook-logs.show', $event->id) }}" class="text-primary-DEFAULT hover:underline">View</a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-4 px-4 text-xs text-gray-500 text-center">No deliveries from CHIP yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
