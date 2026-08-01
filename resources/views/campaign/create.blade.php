<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Campaign</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Create</span>
    </x-slot>

    <x-slot name="title">Create Campaign</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons-outlined mr-2 text-primary-DEFAULT">campaign</span>
                <h1 class="text-xl font-bold text-gray-800">Create Campaign</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">
                Send a message to your participants by email or SMS.
            </p>
        </div>

        <div class="p-4">
            @include('campaign.partials.form', ['campaign' => null])
        </div>
    </div>
</x-app-layout>
