<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $title ?? $survey->title }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined&display=block" rel="stylesheet">

    @vite(['resources/css/app.css'])

    {{-- Appearance settings, so a public survey carries the same branding. --}}
    @include('partials.branding-head')
</head>
<body class="font-sans text-gray-900 antialiased bg-gray-100">
    <div class="min-h-screen py-8 px-4">
        <div class="max-w-2xl mx-auto">
            {{-- Survey header --}}
            <div class="bg-white rounded-t border-t-4 border-primary-DEFAULT shadow-sm p-6">
                <h1 class="text-lg font-semibold text-gray-800">{{ $survey->title }}</h1>

                @if($survey->event)
                    <p class="text-xs text-gray-500 mt-1">{{ $survey->event->name }}</p>
                @endif

                @if($survey->description)
                    <p class="text-xs text-gray-600 mt-3 whitespace-pre-line">{{ $survey->description }}</p>
                @endif
            </div>

            @yield('content')

            <p class="text-center text-[11px] text-gray-400 mt-6">
                Powered by {{ config('app.name', 'SIJIL') }}
            </p>
        </div>
    </div>
</body>
</html>
