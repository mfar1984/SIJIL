@extends('layouts.event-registration')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4">
    <div class="bg-white rounded-xl shadow-md border border-gray-200 max-w-4xl mx-auto">
        <div class="p-8 sm:p-12 text-center">
            <div class="mx-auto mb-6 flex items-center justify-center w-16 h-16 rounded-full bg-green-100">
                <span class="material-icons text-green-600 text-3xl">check_circle</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-2">Thank You!</h1>
            <p class="text-gray-500 text-base sm:text-lg">Your response to <span class="font-semibold">{{ $event->name }}</span> has been submitted successfully.</p>

            <div class="mt-6 bg-green-50 border border-green-200 rounded-md p-5 text-green-800 text-sm">
                <p>Your feedback is valuable to us and will help improve our services.</p>
                <p class="mt-2">If you have any questions related to {{ $event->name }}, please contact the event organizer.</p>
            </div>

            <div class="mt-8">
                <a href="{{ route('event.register', $event->registration_link) }}" class="inline-flex items-center px-4 h-[36px] bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out">
                    <span class="material-icons text-xs mr-1">info</span>
                    Learn more about {{ $event->name }}
                </a>
            </div>

            <div class="mt-10 text-[11px] text-gray-400">© {{ now()->year }} Sijil. All rights reserved.</div>
        </div>
    </div>
</div>
@endsection


