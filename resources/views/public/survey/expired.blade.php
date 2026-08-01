@extends('layouts.survey-public')

@section('content')
    <div class="bg-white rounded-b shadow-sm p-8 text-center">
        <div class="text-amber-500 mb-4">
            <span class="material-icons-outlined" style="font-size: 48px !important; width: 48px; height: 48px;">event_busy</span>
        </div>

        <h2 class="text-base font-semibold text-gray-800">This survey is not accepting responses</h2>
        <p class="text-xs text-gray-500 mt-2 max-w-sm mx-auto">
            {{ $survey->closedReason() ?? 'This survey is currently unavailable.' }}
        </p>

        @if($survey->event && ($survey->event->contact_email || $survey->event->contact_person))
            <div class="mt-6 inline-block text-left bg-gray-50 border border-gray-200 rounded p-4">
                <p class="text-xs text-gray-500 mb-2">For enquiries, contact:</p>

                @if($survey->event->contact_person)
                    <p class="text-xs text-gray-800">{{ $survey->event->contact_person }}</p>
                @endif

                @if($survey->event->contact_email)
                    <p class="text-xs text-gray-800">{{ $survey->event->contact_email }}</p>
                @endif

                @if($survey->event->contact_phone)
                    <p class="text-xs text-gray-800">{{ $survey->event->contact_phone }}</p>
                @endif
            </div>
        @endif
    </div>
@endsection
