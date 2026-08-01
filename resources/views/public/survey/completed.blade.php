@extends('layouts.survey-public')

@section('content')
    <div class="bg-white rounded-b shadow-sm p-8 text-center">
        <div class="text-emerald-500 mb-4">
            <span class="material-icons-outlined" style="font-size: 48px !important; width: 48px; height: 48px;">task_alt</span>
        </div>

        <h2 class="text-base font-semibold text-gray-800">You have already responded</h2>
        <p class="text-xs text-gray-500 mt-2 max-w-sm mx-auto">
            Our records show a response for this survey has already been submitted.
            This survey accepts one response per person.
        </p>
    </div>
@endsection
