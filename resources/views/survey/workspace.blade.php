<x-app-layout>
    <x-slot name="breadcrumb">
        <a href="{{ route('survey.index') }}" class="text-primary-light">Survey</a>
        <span class="mx-2 text-gray-500">/</span>
        <span>{{ Str::limit($survey->title, 40) }}</span>
    </x-slot>

    <x-slot name="title">{{ $survey->title }}</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        @include('survey.partials.workspace-header', ['active' => $tab])

        @if(session('success'))
            <div class="mx-4 mt-4 bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded text-xs">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mx-4 mt-4 bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded text-xs">
                {{ session('error') }}
            </div>
        @endif

        <div class="p-4">
            @switch($tab)
                @case('settings')
                    @include('survey.partials.tab-settings')
                    @break

                @case('share')
                    @include('survey.partials.tab-share')
                    @break

                @default
                    @include('survey.partials.tab-questions')
            @endswitch
        </div>
    </div>
</x-app-layout>
