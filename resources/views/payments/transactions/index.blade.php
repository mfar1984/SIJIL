<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Billing</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Payment</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Transactions</span>
    </x-slot>

    <x-slot name="title">Transactions</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons-outlined mr-2 text-primary-DEFAULT">receipt_long</span>
                <h1 class="text-xl font-bold text-gray-800">Transactions</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">Every package order started from the pricing page, paid or not</p>
        </div>

        <div class="p-4">
            @if (session('success'))
                <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded text-xs">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded text-xs">{{ session('error') }}</div>
            @endif

            @php $filtered = request()->hasAny(['search', 'status', 'package', 'from', 'to']) && collect(request()->only(['search', 'status', 'package', 'from', 'to']))->filter()->isNotEmpty(); @endphp
            <form method="GET" action="{{ route('payments.transactions.index') }}" class="flex flex-wrap items-center gap-2 mb-4">
                <input type="text" name="search" value="{{ request('search') }}" maxlength="255"
                       placeholder="Email, order reference or CHIP purchase id..."
                       aria-label="Search"
                       class="flex-1 min-w-[12rem] h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">

                <select name="status" onchange="this.form.submit()" aria-label="Status"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[10rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Status</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="package" onchange="this.form.submit()" aria-label="Package"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[12rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Packages</option>
                    @foreach($packages as $package)
                        <option value="{{ $package->code }}" @selected(request('package') === $package->code)>{{ $package->name }}</option>
                    @endforeach
                </select>

                <label class="text-xs text-gray-600 flex items-center gap-1 shrink-0">From
                    <input type="date" name="from" value="{{ request('from') }}" class="h-9 text-xs border-gray-300 rounded px-2">
                </label>
                <label class="text-xs text-gray-600 flex items-center gap-1 shrink-0">To
                    <input type="date" name="to" value="{{ request('to') }}" class="h-9 text-xs border-gray-300 rounded px-2">
                </label>

                <button type="submit" title="Search"
                        class="h-9 px-3 bg-primary-DEFAULT hover:bg-primary-dark text-white rounded text-xs flex items-center shrink-0">
                    <span class="material-icons-outlined text-xs">search</span>
                </button>
                @if($filtered)
                    <a href="{{ route('payments.transactions.index') }}" class="text-xs text-gray-500 underline shrink-0">Reset</a>
                @endif
            </form>

            <div class="overflow-x-auto border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left">Created</th>
                            <th class="py-3 px-4 text-left">Reference</th>
                            <th class="py-3 px-4 text-left">Email</th>
                            <th class="py-3 px-4 text-left">Package</th>
                            <th class="py-3 px-4 text-right">Amount</th>
                            <th class="py-3 px-4 text-left">Type</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($orders as $order)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 whitespace-nowrap">{{ \App\Support\SystemSettings::dateTime($order->created_at) }}</td>
                                <td class="py-3 px-4 font-mono">{{ $order->reference }}</td>
                                <td class="py-3 px-4">
                                    @if($order->purged_at)
                                        <span class="text-gray-400 italic">purged</span>
                                    @else
                                        {{ $order->email ?? '—' }}
                                    @endif
                                </td>
                                <td class="py-3 px-4">{{ $order->package_name }}</td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">{{ $order->amountDisplay() }}</td>
                                <td class="py-3 px-4">{{ $order->type === 'renewal' ? 'Renewal' : 'New' }}</td>
                                <td class="py-3 px-4 space-x-1 whitespace-nowrap">
                                    @include('payments.partials.order-status', ['order' => $order])
                                    @if($order->status === 'fulfilled' && $order->type === 'new' && ! $order->set_password_sent_at)
                                        @include('payments.partials.badge', ['tone' => 'amber', 'label' => 'Set-password email not sent'])
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <a href="{{ route('payments.transactions.show', $order) }}" class="inline-block p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View">
                                        <span class="material-icons-outlined text-primary-DEFAULT text-xs">visibility</span>
                                    </a>
                                    @can('payment_transactions.update')
                                        @if($order->isCancellable())
                                            <form method="POST" action="{{ route('payments.transactions.cancel', $order) }}" onsubmit="return confirm('Cancel this order? The buyer will no longer be able to pay it. The record is kept.')" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Cancel order">
                                                    <span class="material-icons-outlined text-red-600 text-xs">cancel</span>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-6 px-4 text-center text-xs text-gray-500">
                                    {{ $filtered ? 'No orders match these filters.' : 'No orders yet. They appear here once a buyer starts a checkout on the pricing page.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $orders->links('components.pagination-modern') }}
            </div>
        </div>
    </div>
</x-app-layout>
