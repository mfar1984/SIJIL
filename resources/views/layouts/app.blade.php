<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script>
            // Expose Firebase public config to SW if needed
            window.FIREBASE_PUBLIC_CONFIG = {
                apiKey: '{{ env('VITE_FIREBASE_API_KEY') }}',
                authDomain: '{{ env('VITE_FIREBASE_AUTH_DOMAIN') }}',
                projectId: '{{ env('VITE_FIREBASE_PROJECT_ID') }}',
                storageBucket: '{{ env('VITE_FIREBASE_STORAGE_BUCKET') }}',
                messagingSenderId: '{{ env('VITE_FIREBASE_MESSAGING_SENDER_ID') }}',
                appId: '{{ env('VITE_FIREBASE_APP_ID') }}',
                measurementId: '{{ env('VITE_FIREBASE_MEASUREMENT_ID') }}',
            };
        </script>

        <title>{{ isset($title) ? $title . ' - ' . config('app.name', 'SIJIL') : config('app.name', 'SIJIL') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        
        <!-- Alpine.js -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Alpine.js x-cloak CSS -->
        <style>
            [x-cloak] { display: none !important; }
        </style>

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
                            <div class="flex items-center justify-center h-8" x-data="{ showNotifications: false, notifications: [], unreadCount: 0 }" id="notification-container" x-on:add-notification.window="notifications.unshift($event.detail); unreadCount++; if (notifications.length > 20) { notifications.pop() }" x-on:set-notifications.window="notifications = $event.detail.notifications || []; unreadCount = $event.detail.unreadCount || 0" x-on:mark-all-read.window="notifications.forEach(n => n.read_at = (new Date()).toISOString()); unreadCount = 0">
                                <button @click="showNotifications = !showNotifications" class="text-gray-500 hover:text-gray-700 flex items-center justify-center relative">
                                    <span class="material-icons text-xl">notifications</span>
                                    <span x-show="unreadCount > 0" x-text="unreadCount" class="absolute -top-2 -right-2 h-4 w-4 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center"></span>
                                </button>
                                
                                <!-- Notifications Dropdown -->
                                <div x-show="showNotifications" @click.away="showNotifications = false" class="absolute right-16 top-16 mt-2 w-80 bg-white rounded-md shadow-lg z-20 border border-gray-200 overflow-hidden">
                                    <div class="py-2 px-3 bg-gray-100 border-b border-gray-200 flex justify-between items-center">
                                        <h3 class="text-xs font-semibold text-gray-700">Notifications</h3>
                                        <span x-show="unreadCount > 0" @click="window.markAllAsRead()" class="text-xs text-blue-500 hover:text-blue-700 cursor-pointer">Mark all as read</span>
                                    </div>
                                    
                                    <div class="max-h-64 overflow-y-auto">
                                        <template x-if="notifications.length === 0">
                                            <div class="py-4 px-3 text-center text-gray-500 text-xs">
                                                <p>No new notifications</p>
                                            </div>
                                        </template>
                                        
                                        <template x-for="notification in notifications" :key="notification.id">
                                            <a :href="notification.url" class="block py-2 px-3 hover:bg-gray-50 border-b border-gray-100 transition duration-150 ease-in-out" :class="{'bg-blue-50': !notification.read_at}">
                                                <div class="flex items-start">
                                                    <div class="flex-shrink-0 mr-2">
                                                        <span class="material-icons text-primary-DEFAULT" x-text="notification.icon || 'forum'"></span>
                                                    </div>
                                                    <div class="flex-grow">
                                                        <p class="text-xs font-medium" x-text="notification.title"></p>
                                                        <p class="text-xs text-gray-500" x-text="notification.message"></p>
                                                        <p class="text-xs text-gray-400 mt-1" x-text="notification.time"></p>
                                                    </div>
                                                    <div x-show="!notification.read_at" class="flex-shrink-0 ml-2">
                                                        <span class="h-2 w-2 rounded-full bg-blue-500 inline-block"></span>
                                                    </div>
                                                </div>
                                            </a>
                                        </template>
                                    </div>
                                    
                                    <a href="{{ route('helpdesk.index') }}" class="block text-center py-2 text-xs text-primary-DEFAULT hover:bg-gray-50 font-medium">
                                        View all notifications
                                    </a>
                                </div>
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
        
        <!-- Notifications System -->
        <script>
            // Debug disabled for production
            function debugLog(message, data = null) {
                // Debug logs removed
            }
        
            document.addEventListener('DOMContentLoaded', function() {
                // Prepare notification sound and unlock on first user gesture
                try { window.__notifAudio = new Audio('/sounds/notification.mp3'); window.__notifAudio.preload = 'auto'; } catch(_) {}
                window.__audioUnlocked = false;
                document.addEventListener('click', function onFirstClick() {
                    window.__audioUnlocked = true;
                    try { if (window.__notifAudio) { window.__notifAudio.play().then(()=>{ window.__notifAudio.pause(); window.__notifAudio.currentTime = 0; }).catch(()=>{}); } } catch(_) {}
                    document.removeEventListener('click', onFirstClick);
                }, { once: true });
                // Notification system initializing
                
                // Check if Alpine.js is loaded
                if (typeof Alpine === 'undefined') {
                    console.error('[NOTIFICATION] Alpine.js is not loaded! Notifications will not work properly.');
                }
                
                // Get notification container
                const notificationContainer = document.getElementById('notification-container');
                if (!notificationContainer) {
                    console.error('[NOTIFICATION] Notification container not found!');
                    return;
                }
                
                // Function to add notification
                function addNotification(notification) {
                    // Adding notification
                    
                    try {
                        if (notificationContainer.__x) {
                            const data = notificationContainer.__x.$data;
                            
                            // Add notification to the list and increase unread count
                            data.notifications.unshift(notification);
                            data.unreadCount++;
                            
                            // Limit notifications to 20 in the UI
                            if (data.notifications.length > 20) {
                                data.notifications.pop();
                            }
                            
                            // Notification added
                            try { if (window.__audioUnlocked && window.__notifAudio) { window.__notifAudio.currentTime = 0; window.__notifAudio.play().catch(() => {}); } } catch(_) {}
                        } else {
                            console.error('[NOTIFICATION] Alpine.js component not initialized on notification container');
                        }
                    } catch (error) {
                        console.error('[NOTIFICATION] Error adding notification:', error);
                    }
                }
                
                // Function to mark all as read
                window.markAllAsRead = function() {
                    // Marking all as read
                    
                    fetch('/helpdesk/notifications/mark-read', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(() => {
                        if (notificationContainer.__x) {
                            const data = notificationContainer.__x.$data;
                            const nowIso = new Date().toISOString();
                            // Reassign array to ensure Alpine reactivity updates
                            data.notifications = (data.notifications || []).map(n => Object.assign({}, n, { read_at: nowIso }));
                            data.unreadCount = 0;
                            window.__lastUnreadCount = 0;
                            // All notifications marked as read
                        }
                        // Force a fresh pull from server to keep UI consistent with backend
                        return fetch('/helpdesk/notifications');
                    })
                    .then(r => r && r.ok ? r.json() : null)
                    .then(fresh => {
                        if (fresh && notificationContainer.__x) {
                            const cd = notificationContainer.__x.$data;
                            cd.notifications = fresh.notifications || [];
                            cd.unreadCount = fresh.unreadCount || 0;
                            window.__lastUnreadCount = cd.unreadCount;
                        }
                    })
                    .catch(error => console.error('[NOTIFICATION] Error marking all as read:', error));
                };
                
                // Fetch initial notifications
                // Fetching initial notifications
                fetch('/helpdesk/notifications')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Initial notifications received
                        
                        // Update Alpine component directly if ready
                        if (notificationContainer.__x) {
                            const componentData = notificationContainer.__x.$data;
                            componentData.notifications = data.notifications || [];
                            componentData.unreadCount = data.unreadCount || 0;
                            window.__lastUnreadCount = componentData.unreadCount;
                        }
                        // Always dispatch event so Alpine can react even if not initialized yet
                        try {
                            window.dispatchEvent(new CustomEvent('set-notifications', { detail: {
                                notifications: data.notifications || [],
                                unreadCount: data.unreadCount || 0
                            }}));
                        } catch(_) {}
                    })
                    .catch(error => console.error('[NOTIFICATION] Error fetching notifications:', error));
                
                // Real-time handled by Firebase Messaging (onMessage in resources/js/fcm.js)
                // Fallback polling: refresh bell every 30s, play sound on increase
                setInterval(() => {
                    fetch('/helpdesk/notifications')
                        .then(r => r.json())
                        .then(data => {
                            if (notificationContainer.__x) {
                                const cd = notificationContainer.__x.$data;
                                const prev = typeof window.__lastUnreadCount === 'number' ? window.__lastUnreadCount : cd.unreadCount;
                                cd.notifications = data.notifications || [];
                                cd.unreadCount = data.unreadCount || 0;
                                if ((data.unreadCount || 0) > prev) {
                                    try { if (window.__audioUnlocked && window.__notifAudio) { window.__notifAudio.currentTime = 0; window.__notifAudio.play().catch(() => {}); } } catch(_) {}
                                }
                                window.__lastUnreadCount = cd.unreadCount;
                            }
                            // Dispatch update event as a fallback to ensure UI updates
                            try {
                                window.dispatchEvent(new CustomEvent('set-notifications', { detail: {
                                    notifications: data.notifications || [],
                                    unreadCount: data.unreadCount || 0
                                }}));
                            } catch(_) {}
                        })
                        .catch(() => {});
                }, 10000);
                
                // Notification system initialization complete
            });
        </script>
    </body>
</html>
