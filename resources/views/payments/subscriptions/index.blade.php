<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Billing</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Payment</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Subscriptions</span>
    </x-slot>

    <x-slot name="title">Subscriptions</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons-outlined mr-2 text-primary-DEFAULT">card_membership</span>
                <h1 class="text-xl font-bold text-gray-800">Subscriptions</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">Accounts that hold a package. Permanent accounts have no expiry and are not listed.</p>
        </div>

        <div class="p-4">
            @php $filtered = collect(request()->only(['search', 'state', 'package']))->filter()->isNotEmpty(); @endphp
            <form method="GET" action="{{ route('payments.subscriptions.index') }}" class="flex flex-wrap items-center gap-2 mb-4">
                <input type="text" name="search" value="{{ request('search') }}" maxlength="255" aria-label="Search"
                       placeholder="Search name or email..."
                       class="flex-1 min-w-[12rem] h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">

                <select name="state" onchange="this.form.submit()" aria-label="State"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[13rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All States</option>
                    @foreach($states as $value => $label)
                        <option value="{{ $value }}" @selected(request('state') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="package" onchange="this.form.submit()" aria-label="Package"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[12rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Packages</option>
                    @foreach($packages as $package)
                        <option value="{{ $package->code }}" @selected(request('package') === $package->code)>{{ $package->name }}</option>
                    @endforeach
                </select>

                <button type="submit" title="Search"
                        class="h-9 px-3 bg-primary-DEFAULT hover:bg-primary-dark text-white rounded text-xs flex items-center shrink-0">
                    <span class="material-icons-outlined text-xs">search</span>
                </button>
                @if($filtered)
                    <a href="{{ route('payments.subscriptions.index') }}" class="text-xs text-gray-500 underline shrink-0">Reset</a>
                @endif
            </form>

            <div class="overflow-x-auto border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left">User</th>
                            <th class="py-3 px-4 text-left">Package</th>
                            <th class="py-3 px-4 text-left">Start</th>
                            <th class="py-3 px-4 text-left">Expiry</th>
                            <th class="py-3 px-4 text-left">State</th>
                            <th class="py-3 px-4 text-left">Source</th>
                            <th class="py-3 px-4 text-left">Password set</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($users as $user)
                            @php $state = \App\Http\Controllers\Payments\SubscriptionController::stateOf($user->package_expires_at); @endphp
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    @can('users.read')
                                        <a href="{{ route('user.show', $user->id) }}" class="font-medium text-primary-DEFAULT hover:underline">{{ $user->name }}</a>
                                    @else
                                        <span class="font-medium">{{ $user->name }}</span>
                                    @endcan
                                    <div class="text-gray-500">{{ $user->email }}</div>
                                    @if(! $user->hasRole('Organizer'))
                                        <div class="text-amber-700">Not an Organizer: the package does not apply.</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4">{{ $user->package?->name ?? '—' }}</td>
                                <td class="py-3 px-4 whitespace-nowrap">{{ \App\Support\SystemSettings::date($user->package_started_at) }}</td>
                                <td class="py-3 px-4 whitespace-nowrap">{{ \App\Support\SystemSettings::dateTime($user->package_expires_at) }}</td>
                                <td class="py-3 px-4">
                                    @include('payments.partials.badge', [
                                        'tone' => ['active' => 'green', 'expiring' => 'amber', 'expired' => 'red'][$state] ?? 'gray',
                                        'label' => ['active' => 'Active', 'expiring' => 'Expiring', 'expired' => 'Expired'][$state] ?? '—',
                                    ])
                                </td>
                                <td class="py-3 px-4">{{ ['purchase' => 'Purchase', 'admin' => 'Administrator'][$user->package_source] ?? '—' }}</td>
                                <td class="py-3 px-4">{{ $user->password_changed_at ? 'Yes' : 'No' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 px-4 text-center text-xs text-gray-500">
                                    {{ $filtered ? 'No subscriptions match these filters.' : 'No account holds a package yet. Every account is Permanent.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $users->links('components.pagination-modern') }}
            </div>
        </div>
    </div>
</x-app-layout>
