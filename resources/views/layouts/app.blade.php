<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' - ' . config('app.name', 'SIJIL') : config('app.name', 'SIJIL') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-50 flex">
            <!-- Sidebar -->
            @include('layouts.sidebar')

            <!-- Main Content -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Top Navigation -->
                <header class="bg-white shadow-lg z-10">
                    <div class="flex justify-between items-center px-4 py-3">
                        <!-- Welcome & Date/Time -->
                        <div class="flex items-center">
                            <div class="text-xs">
                                <span class="font-medium">Welcome, {{ Auth::user()->name }}</span>
                                <span class="mx-2 text-gray-400">|</span>
                                <span id="current-date-time" class="text-gray-500"></span>
                            </div>
                        </div>

                        <!-- Notifications & User Menu -->
                        <div class="flex items-center space-x-4">
                            <!-- Notifications -->
                            <div class="flex items-center justify-center h-8">
                                <button class="text-gray-500 hover:text-gray-700 flex items-center justify-center relative">
                                    <span class="material-icons text-xl">notifications</span>
                                    <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
                                </button>
                            </div>

                            <!-- User Menu -->
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="flex items-center text-xs font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none transition duration-150 ease-in-out">
                                        <div class="flex items-center space-x-2">
                                            <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                                <span class="material-icons text-xs text-gray-500">person</span>
                                            </div>
                                            <div class="hidden md:flex">
                                                <span class="text-xs">{{ Auth::user()->name }}</span>
                                            </div>
                                        </div>

                                        <div class="ml-1">
                                            <svg class="fill-current h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('profile.edit')">
                                        <div class="flex items-center space-x-2">
                                            <span class="material-icons text-xs">account_circle</span>
                                            <span class="text-xs">{{ __('Profile') }}</span>
                                        </div>
                                    </x-dropdown-link>

                                    <!-- Authentication -->
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <x-dropdown-link :href="route('logout')"
                                                onclick="event.preventDefault();
                                                            this.closest('form').submit();">
                                            <div class="flex items-center space-x-2">
                                                <span class="material-icons text-xs">logout</span>
                                                <span class="text-xs">{{ __('Log Out') }}</span>
                                            </div>
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>

                    <!-- Breadcrumb -->
                    <div class="px-4 py-2 bg-gray-50 border-t border-gray-100">
                        <nav class="flex items-center text-xs">
                            <a href="{{ route('dashboard') }}" class="text-primary-light flex items-center">
                                <span class="material-icons text-xs">home</span>
                                <span class="ml-1">Home</span>
                            </a>
                            
                            @if(isset($breadcrumb))
                                <span class="mx-2 text-gray-500">/</span>
                                {{ $breadcrumb }}
                            @endif
                        </nav>
                    </div>
                </header>

                <!-- Page Heading -->
                @if (isset($header))
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

            <!-- Page Content -->
                <main class="flex-1 overflow-auto p-4">
                {{ $slot }}
            </main>
            </div>
        </div>

        <script>
            // Set up the real-time clock
            function updateDateTime() {
                const now = new Date();
                const options = { 
                    weekday: 'long', 
                    day: 'numeric',
                    month: 'long', 
                    year: 'numeric'
                };
                
                const date = now.toLocaleDateString('en-US', options);
                
                // Get time in 24-hour format
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                
                const formattedDateTime = date.replace(',', '') + ' at ' + hours + ':' + minutes + ':' + seconds;
                document.getElementById('current-date-time').textContent = formattedDateTime;
            }
            
            // Update time immediately and then every second
            updateDateTime();
            setInterval(updateDateTime, 1000);
        </script>
    </body>
</html>
