@extends('layouts.survey-public')

@section('content')
    @if($errors->any())
        <div class="bg-red-50 border-x border-red-200 px-6 py-3">
            <p class="text-xs font-medium text-red-700 mb-1">Please check the following:</p>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li class="text-xs text-red-700">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-amber-50 border-x border-amber-200 px-6 py-3 text-xs text-amber-800">
            {{ session('error') }}
        </div>
    @endif

    @if($participant)
        <div class="bg-blue-50 border-x border-blue-200 px-6 py-3 flex items-center gap-2">
            <span class="material-icons-outlined text-blue-600 text-base">how_to_reg</span>
            <span class="text-xs text-blue-800">
                Answering as <span class="font-medium">{{ $participant->name }}</span>
            </span>
        </div>
    @endif

    <form method="POST" action="{{ route('public.survey.submit', $survey->slug) }}" class="space-y-3">
        @csrf

        {{-- Respondent details, only when the survey asks for them and the person
             has not already been identified as a participant. --}}
        @if($survey->require_respondent_details && ! $participant)
            <div class="bg-white shadow-sm p-6 space-y-4">
                <h2 class="text-sm font-medium text-gray-800">Your details</h2>

                <div>
                    <label for="respondent_name" class="block text-xs font-medium text-gray-700 mb-1">
                        Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="respondent_name" id="respondent_name"
                           value="{{ old('respondent_name') }}" required
                           class="w-full text-xs border-gray-300 rounded px-3 py-2 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                </div>

                <div>
                    <label for="respondent_email" class="block text-xs font-medium text-gray-700 mb-1">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="respondent_email" id="respondent_email"
                           value="{{ old('respondent_email') }}" required
                           class="w-full text-xs border-gray-300 rounded px-3 py-2 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                </div>

                <div>
                    <label for="respondent_phone" class="block text-xs font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="respondent_phone" id="respondent_phone"
                           value="{{ old('respondent_phone') }}"
                           class="w-full text-xs border-gray-300 rounded px-3 py-2 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                </div>
            </div>
        @endif

        @foreach($survey->questions as $question)
            @php $field = 'question_' . $question->id; @endphp

            <div class="bg-white shadow-sm p-6 {{ $errors->has($field) ? 'border-l-4 border-red-400' : '' }}">
                <label class="block text-sm text-gray-800">
                    {{ $loop->iteration }}. {{ $question->question_text }}
                    @if($question->required)
                        <span class="text-red-500">*</span>
                    @endif
                </label>

                @if($question->description)
                    <p class="text-xs text-gray-500 mt-1">{{ $question->description }}</p>
                @endif

                <div class="mt-4">
                    @include('public.survey.partials.question-input', ['question' => $question, 'field' => $field])
                </div>

                @error($field)
                    <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                @enderror
            </div>
        @endforeach

        <div class="bg-white rounded-b shadow-sm p-6 flex items-center justify-between">
            <p class="text-[11px] text-gray-400">
                @if($survey->expires_at)
                    Closes {{ $survey->expires_at->format('d M Y, H:i') }}
                @else
                    Your answers are submitted once you press Submit.
                @endif
            </p>
            <button type="submit"
                    class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-4 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                <span class="material-icons-outlined text-xs mr-1">send</span>
                Submit
            </button>
        </div>
    </form>
@endsection
