@extends('layouts.survey-public')

@section('content')
    <div class="bg-white rounded-b shadow-sm p-8 text-center">
        <div class="text-emerald-500 mb-4">
            <span class="material-icons-outlined" style="font-size: 48px !important; width: 48px; height: 48px;">check_circle</span>
        </div>

        <h2 class="text-base font-semibold text-gray-800">Thank you</h2>
        <p class="text-xs text-gray-500 mt-2 max-w-sm mx-auto">
            Your response has been recorded. We appreciate you taking the time to give us your feedback.
        </p>

        @if($survey->event)
            <p class="text-xs text-gray-400 mt-4">{{ $survey->event->name }}</p>
        @endif
    </div>
@endsection
