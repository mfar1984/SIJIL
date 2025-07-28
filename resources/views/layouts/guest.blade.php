<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-row bg-gray-100">
            <!-- Left: 70% -->
            <div class="hidden md:flex w-0 md:w-[70%] items-center justify-center bg-white relative" style="background: url('/images/background-login.gif') center center / cover no-repeat; background-color: #fff;">
                <!-- No more Rive animation or welcome text -->
                <!-- Vertical shadow divider (true shadow, not solid line) -->
                <div class="absolute top-0 right-0 h-full w-6 pointer-events-none" style="box-shadow: 4px 0 32px -12px rgba(0,0,0,0.07);"></div>
            </div>
            <!-- Right: 30% -->
            <div class="flex w-full md:w-[30%] min-h-screen items-center justify-center bg-white shadow-lg font-poppins">
                <div class="w-full max-w-md p-8">
                    <div class="flex justify-center mb-6 flex-col items-center">
                        <a href="/" class="mb-0">
                            <img src="/images/logo.png" alt="Logo" class="w-40 h-40 object-contain mb-0" />
                        </a>
                        <span class="mt-0 mb-0 text-2xl font-bold text-gray-800 font-data70" style="line-height:1;">E-Certificate</span>
                    </div>
                    {{ $slot }}
                </div>
            </div>
        </div>
        <style>
            @font-face {
                font-family: 'Data 70';
                src: url('/assets/fonts/Data70.ttf') format('truetype');
                font-weight: normal;
                font-style: normal;
            }
            .font-data70 {
                font-family: 'Data 70', monospace;
                letter-spacing: 0.05em;
            }
        </style>
    </body>
</html>
