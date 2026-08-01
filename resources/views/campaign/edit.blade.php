<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Campaign</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Edit</span>
    </x-slot>

    <x-slot name="title">Edit Campaign</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons-outlined mr-2 text-primary-DEFAULT">campaign</span>
                        <h1 class="text-xl font-bold text-gray-800">Edit Campaign</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">{{ $campaign->name }}</p>
                </div>

                @php
                    $badge = match ($campaign->status) {
                        \App\Models\Campaign::STATUS_COMPLETED => 'bg-green-100 text-green-700',
                        \App\Models\Campaign::STATUS_RUNNING => 'bg-blue-100 text-blue-700',
                        \App\Models\Campaign::STATUS_SCHEDULED => 'bg-amber-100 text-amber-800',
                        default => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <span class="px-2 py-1 rounded text-[11px] font-medium {{ $badge }}">
                    {{ ucfirst($campaign->status) }}
                </span>
            </div>
        </div>

        <div class="p-4">
            @unless($campaign->isSendable())
                <div class="bg-amber-50 border border-amber-200 text-amber-800 px-3 py-2 rounded mb-3 text-xs">
                    This campaign has already been sent. Changes are saved but will not be delivered again.
                </div>
            @endunless

            @include('campaign.partials.form')
        </div>
    </div>
</x-app-layout>
