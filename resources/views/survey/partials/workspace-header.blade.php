{{--
    Shared header and tab bar for every survey screen.

    Expects: $survey (with questions_count and completed_responses_count loaded),
             $active (questions|settings|share|responses|analytics)

    Tabs link to real routes so each one keeps its own permission middleware.
--}}
@php
    $questionCount = $survey->questions_count ?? $survey->questions()->count();
    $responseCount = $survey->completed_responses_count ?? 0;
    $blockers = $survey->publishBlockers();
    $isPublished = $survey->status === 'published';

    $tabClass = fn (bool $on) => $on
        ? 'border-primary-DEFAULT text-primary-DEFAULT'
        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300';

    $statusStyles = match ($survey->status_label) {
        'Accepting responses' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'Draft' => 'bg-amber-50 text-amber-700 border-amber-200',
        'Scheduled' => 'bg-blue-50 text-blue-700 border-blue-200',
        default => 'bg-gray-100 text-gray-600 border-gray-200',
    };
@endphp

<div class="p-6 border-b border-gray-200">
    <div class="flex justify-between items-start gap-4">
        <div class="min-w-0">
            <div class="flex items-center">
                <span class="material-icons-outlined mr-2 text-primary-DEFAULT">poll</span>
                <h1 class="text-xl font-bold text-gray-800 truncate">{{ $survey->title }}</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">
                {{ $survey->event->name ?? 'Not linked to an event' }}
                <span class="mx-1 text-gray-300">|</span>
                {{ $questionCount }} {{ Str::plural('question', $questionCount) }}
                <span class="mx-1 text-gray-300">|</span>
                {{ $responseCount }} {{ Str::plural('response', $responseCount) }}
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <span class="text-[11px] px-2 py-1 rounded-full border {{ $statusStyles }}">
                {{ $survey->status_label }}
            </span>

            @can('surveys.publish')
            <form method="POST" action="{{ route('survey.toggle-publish', $survey) }}">
                @csrf
                @if($isPublished)
                    <button type="submit"
                            class="bg-gradient-to-r from-gray-600 to-gray-500 hover:from-gray-700 hover:to-gray-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">unpublished</span>
                        Unpublish
                    </button>
                @else
                    <button type="submit" {{ empty($blockers) ? '' : 'disabled' }}
                            class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out disabled:opacity-40 disabled:cursor-not-allowed">
                        <span class="material-icons-outlined text-xs mr-1">publish</span>
                        Publish
                    </button>
                @endif
            </form>
            @endcan

            <a href="{{ route('survey.index') }}"
               class="bg-gradient-to-r from-gray-500 to-gray-400 hover:from-gray-600 hover:to-gray-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                <span class="material-icons-outlined text-xs mr-1">arrow_back</span>
                Back
            </a>
        </div>
    </div>

    {{-- What needs doing before this survey can go live --}}
    @if(! $isPublished && ! empty($blockers))
        <div class="mt-4 ml-8 bg-amber-50 border border-amber-200 rounded px-3 py-2">
            <p class="text-xs text-amber-800">
                <span class="font-medium">Before publishing:</span>
                {{ implode(' ', $blockers) }}
            </p>
        </div>
    @endif
</div>

<div class="border-b border-gray-200 px-4">
    <div class="flex flex-wrap -mb-px">
        <a href="{{ route('survey.show', [$survey, 'tab' => 'questions']) }}"
           class="inline-flex items-center whitespace-nowrap py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out {{ $tabClass($active === 'questions') }}">
            <span class="material-icons-outlined text-xs mr-2">quiz</span>
            Questions
            @if($questionCount === 0)
                <span class="ml-2 bg-red-100 text-red-700 text-[10px] px-1.5 rounded-full">0</span>
            @else
                <span class="ml-2 bg-gray-100 text-gray-600 text-[10px] px-1.5 rounded-full">{{ $questionCount }}</span>
            @endif
        </a>

        <a href="{{ route('survey.show', [$survey, 'tab' => 'settings']) }}"
           class="inline-flex items-center whitespace-nowrap py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out {{ $tabClass($active === 'settings') }}">
            <span class="material-icons-outlined text-xs mr-2">tune</span>
            Settings
        </a>

        <a href="{{ route('survey.show', [$survey, 'tab' => 'share']) }}"
           class="inline-flex items-center whitespace-nowrap py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out {{ $tabClass($active === 'share') }}">
            <span class="material-icons-outlined text-xs mr-2">share</span>
            Share
        </a>

        @can('survey_responses.read')
        <a href="{{ route('survey.responses', $survey) }}"
           class="inline-flex items-center whitespace-nowrap py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out {{ $tabClass($active === 'responses') }}">
            <span class="material-icons-outlined text-xs mr-2">format_list_bulleted</span>
            Responses
            @if($responseCount > 0)
                <span class="ml-2 bg-gray-100 text-gray-600 text-[10px] px-1.5 rounded-full">{{ $responseCount }}</span>
            @endif
        </a>

        <a href="{{ route('survey.analytics', $survey) }}"
           class="inline-flex items-center whitespace-nowrap py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out {{ $tabClass($active === 'analytics') }}">
            <span class="material-icons-outlined text-xs mr-2">insights</span>
            Analytics
        </a>
        @endcan
    </div>
</div>
