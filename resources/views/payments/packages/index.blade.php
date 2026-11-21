<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Billing</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Payment</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Packages</span>
    </x-slot>

    <x-slot name="title">Packages</x-slot>

    @php $canEdit = auth()->user()->can('payment_packages.update'); @endphp

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons-outlined mr-2 text-primary-DEFAULT">inventory_2</span>
                <h1 class="text-xl font-bold text-gray-800">Packages</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">
                The price list used by the pricing page and charged through CHIP. A price change applies to new orders only; orders already started keep the price the buyer saw.
                Packages cannot be deleted. Make one inactive to stop selling it.
            </p>
        </div>

        <div class="p-4">
            @if (session('success'))
                <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded text-xs">{{ session('success') }}</div>
            @endif

            <div class="overflow-x-auto border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left">Code</th>
                            <th class="py-3 px-4 text-left">Category</th>
                            <th class="py-3 px-4 text-left">Duration</th>
                            <th class="py-3 px-4 text-left">Name</th>
                            <th class="py-3 px-4 text-left">Price (RM)</th>
                            <th class="py-3 px-4 text-center">Active</th>
                            <th class="py-3 px-4 text-right">Accounts</th>
                            @if($canEdit)
                                <th class="py-3 px-4 text-center">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($packages as $package)
                            @php
                                $bag = $errors->getBag('package'.$package->id);
                                $hasOld = $bag->any();
                                $price = $hasOld ? old('price') : number_format($package->price_cents / 100, $package->price_cents % 100 ? 2 : 0, '.', '');
                            @endphp
                            <tr class="text-xs hover:bg-gray-50 align-top">
                                <td class="py-3 px-4 font-mono whitespace-nowrap">{{ $package->code }}</td>
                                <td class="py-3 px-4">{{ $package->categoryLabel() }}</td>
                                <td class="py-3 px-4 whitespace-nowrap">{{ $package->duration_months }} {{ \Illuminate\Support\Str::plural('month', $package->duration_months) }}</td>
                                @if($canEdit)
                                    <td class="py-3 px-4">
                                        <input form="package-form-{{ $package->id }}" type="text" name="name" maxlength="100" required
                                               value="{{ $hasOld ? old('name') : $package->name }}" aria-label="Name of {{ $package->code }}"
                                               class="w-full min-w-[10rem] h-8 text-xs border-gray-300 rounded px-2 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                        @if($bag->has('name'))<p class="text-[11px] text-red-600 mt-1">{{ $bag->first('name') }}</p>@endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <input form="package-form-{{ $package->id }}" type="text" name="price" inputmode="decimal" maxlength="9" required
                                               value="{{ $price }}" aria-label="Price of {{ $package->code }} in ringgit"
                                               class="w-24 h-8 text-xs border-gray-300 rounded px-2 text-right focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                        @if($bag->has('price'))<p class="text-[11px] text-red-600 mt-1">{{ $bag->first('price') }}</p>@endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <input form="package-form-{{ $package->id }}" type="checkbox" name="is_active" value="1"
                                               @checked($hasOld ? old('is_active') : $package->is_active) aria-label="{{ $package->code }} is active"
                                               class="rounded border-gray-300 text-primary-DEFAULT focus:ring-primary-light h-4 w-4">
                                    </td>
                                @else
                                    <td class="py-3 px-4">{{ $package->name }}</td>
                                    <td class="py-3 px-4">{{ $package->priceDisplay() }}</td>
                                    <td class="py-3 px-4 text-center">
                                        @include('payments.partials.badge', $package->is_active ? ['tone' => 'green', 'label' => 'Active'] : ['tone' => 'gray', 'label' => 'Inactive'])
                                    </td>
                                @endif
                                <td class="py-3 px-4 text-right">{{ $package->users_count }}</td>
                                @if($canEdit)
                                    <td class="py-3 px-4 text-center">
                                        <form id="package-form-{{ $package->id }}" method="POST" action="{{ route('payments.packages.update', $package) }}">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit"
                                                    class="px-3 h-8 bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded shadow-sm text-xs font-medium inline-flex items-center">
                                                <span class="material-icons-outlined text-xs mr-1">save</span>
                                                Save
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
