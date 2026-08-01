@extends('layouts.survey-public')

@section('content')
    <div class="bg-white rounded-b shadow-sm p-6">
        @if(session('error'))
            <div class="bg-amber-50 border border-amber-200 text-amber-800 text-xs px-3 py-2 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex items-start gap-3 mb-5">
            <span class="material-icons-outlined text-primary-DEFAULT">badge</span>
            <div>
                <h2 class="text-sm font-medium text-gray-800">Confirm your registration</h2>
                <p class="text-xs text-gray-500 mt-1">
                    This survey is only for people registered for
                    <span class="font-medium">{{ $survey->event->name ?? 'this event' }}</span>.
                    Enter the email or IC/passport number you used when registering.
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('public.survey.identify', $survey->slug) }}" class="space-y-4">
            @csrf

            <div>
                <label for="identifier" class="block text-xs font-medium text-gray-700 mb-1">
                    Email or IC / Passport number
                </label>
                <input type="text" name="identifier" id="identifier" value="{{ old('identifier') }}" required autofocus
                       class="w-full text-xs border-gray-300 rounded px-3 py-2 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">

                @error('identifier')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-4 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                    <span class="material-icons-outlined text-xs mr-1">arrow_forward</span>
                    Continue
                </button>
            </div>
        </form>
    </div>
@endsection
