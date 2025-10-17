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
            <!-- Left: 70% - Animated Background -->
            <div class="hidden md:flex w-0 md:w-[70%] items-center justify-center bg-gradient-animated relative overflow-hidden">
                <!-- Animated gradient background -->
                <div class="absolute inset-0 bg-gradient-animated-layer"></div>
                
                <!-- Floating shapes for visual interest -->
                <div class="absolute top-20 left-20 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl animate-float-slow"></div>
                <div class="absolute bottom-20 right-20 w-80 h-80 bg-blue-300 opacity-20 rounded-full blur-3xl animate-float-medium"></div>
                <div class="absolute top-1/2 left-1/3 w-48 h-48 bg-blue-200 opacity-15 rounded-full blur-2xl animate-float-fast"></div>
                
                <!-- Logo/Branding -->
                <div class="relative z-10 text-center text-white px-8">
                    <div class="w-40 h-40 mx-auto mb-6 bg-white rounded-2xl shadow-2xl flex items-center justify-center p-4">
                        <img src="/images/logo.png" alt="Logo" class="w-full h-full object-contain" />
                    </div>
                    <h1 class="text-5xl font-bold mb-4 drop-shadow-lg">SIJIL</h1>
                    <p class="text-xl opacity-90">E-Certificate Management System</p>
                    <div class="mt-8 text-sm opacity-75">Secure • Efficient • Professional</div>
                </div>
                
                <!-- Vertical separator line -->
                <div class="absolute top-0 right-0 h-full w-px bg-gradient-to-b from-transparent via-white/30 to-transparent"></div>
            </div>
            <!-- Right: 30% - Login Form -->
            <div class="flex w-full md:w-[30%] min-h-screen items-center justify-center bg-white font-poppins relative" style="box-shadow: -8px 0 32px -12px rgba(0,0,0,0.12);">
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
            
            /* Animated Gradient Background */
            .bg-gradient-animated {
                background: linear-gradient(-45deg, #004aad, #3d7cc9, #2563eb, #1e40af);
                background-size: 400% 400%;
                animation: gradient-shift 15s ease infinite;
            }
            
            .bg-gradient-animated-layer {
                background: linear-gradient(135deg, rgba(0,74,173,0.9) 0%, rgba(61,124,201,0.8) 50%, rgba(37,99,235,0.9) 100%);
                animation: gradient-pulse 8s ease-in-out infinite;
            }
            
            @keyframes gradient-shift {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }
            
            @keyframes gradient-pulse {
                0%, 100% { opacity: 0.9; }
                50% { opacity: 1; }
            }
            
            /* Floating animations for shapes */
            @keyframes float-slow {
                0%, 100% { transform: translate(0, 0) scale(1); }
                33% { transform: translate(30px, -30px) scale(1.1); }
                66% { transform: translate(-20px, 20px) scale(0.9); }
            }
            
            @keyframes float-medium {
                0%, 100% { transform: translate(0, 0) rotate(0deg); }
                50% { transform: translate(-40px, -40px) rotate(180deg); }
            }
            
            @keyframes float-fast {
                0%, 100% { transform: translate(0, 0); }
                25% { transform: translate(20px, -20px); }
                50% { transform: translate(-15px, -30px); }
                75% { transform: translate(-25px, 15px); }
            }
            
            .animate-float-slow {
                animation: float-slow 20s ease-in-out infinite;
            }
            
            .animate-float-medium {
                animation: float-medium 15s ease-in-out infinite;
            }
            
            .animate-float-fast {
                animation: float-fast 10s ease-in-out infinite;
            }
        </style>
    </body>
</html>
