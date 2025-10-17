@extends('layouts.event-registration')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <!-- Event Header -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 mr-4">
                        <span class="material-icons text-white text-3xl">event_busy</span>
                    </div>
                    <h1 class="text-white text-xl md:text-2xl font-bold leading-tight">
                        {{ $event->name }}
                    </h1>
                </div>
            </div>

            <div class="p-6">
                <div class="text-center py-8">
                    <div class="mb-6 text-red-500">
                        <span class="material-icons text-6xl">access_time</span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Registration Expired</h2>
                    <p class="text-gray-600 mb-6">
                        The registration period for this event has ended. The event has already started or is completed.
                    </p>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-6 text-center">
                        <div class="flex justify-center mb-3">
                            <span class="material-icons text-gray-500 text-4xl">calendar_today</span>
                        </div>
                        <div class="text-gray-700 text-base font-semibold">
                            {{ \Carbon\Carbon::parse($event->start_date)->format('d M Y') }}
                            {{ $event->start_time ? ' - ' . substr($event->start_time, 0, 5) : '' }}
                        </div>
                        @if($event->end_date || $event->end_time)
                        <div class="text-gray-500 text-sm mt-1">
                            @if($event->end_date)
                                {{ \Carbon\Carbon::parse($event->end_date)->format('d M Y') }}
                            @endif
                            @if($event->end_time)
                                {{ $event->end_time ? ' - ' . substr($event->end_time, 0, 5) : '' }}
                            @endif
                        </div>
                        @endif
                    </div>
                    <p class="text-gray-600">For more information about this event, please contact:</p>
                    <div class="mt-4 text-gray-800 max-w-md mx-auto bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <div class="space-y-1">
                            @if($event->contact_person)
                            <div class="flex items-start text-sm">
                                <span class="font-medium inline-block w-24">Contact</span>
                                <span class="mx-1">:</span>
                                <span class="text-gray-800">{{ $event->contact_person }}</span>
                            </div>
                            @endif
                            @if($event->contact_email)
                            <div class="flex items-start text-sm">
                                <span class="font-medium inline-block w-24">Email</span>
                                <span class="mx-1">:</span>
                                <span class="text-gray-800">{{ $event->contact_email }}</span>
                            </div>
                            @endif
                            @if($event->contact_phone)
                            <div class="flex items-start text-sm">
                                <span class="font-medium inline-block w-24">Phone</span>
                                <span class="mx-1">:</span>
                                <span class="text-gray-800">{{ $event->contact_phone }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 