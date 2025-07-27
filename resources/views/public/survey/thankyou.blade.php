<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - Survey Completed</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#3b82f6',
                            dark: '#1d4ed8',
                        },
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
    </style>
</head>
<body>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-16">
        <!-- Header with Logo -->
        <div class="mb-8 text-center">
            <div class="flex justify-center">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-12">
            </div>
        </div>
        
        <!-- Thank You Container -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="p-8 text-center">
                <div class="flex justify-center mb-6">
                    <span class="material-icons text-5xl text-green-500">check_circle</span>
                </div>
                
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Thank You!</h1>
                
                <p class="text-lg text-gray-600 mb-6">
                    Your response to <span class="font-semibold">{{ $survey->title }}</span> has been submitted successfully.
                </p>
                
                <div class="max-w-lg mx-auto bg-green-50 rounded-lg p-4 mb-8 text-sm text-green-800">
                    <p>Your feedback is valuable to us and will help improve our services.</p>
                    @if($survey->event)
                    <p class="mt-2">If you have any questions related to {{ $survey->event->name }}, please contact the event organizer.</p>
                    @endif
                </div>
                
                <div class="mt-8">
                    <a href="{{ url('/') }}" class="bg-primary-DEFAULT hover:bg-primary-dark text-white py-2 px-6 rounded shadow-sm font-medium text-sm inline-block">
                        Return to Homepage
                    </a>
                </div>
                
                @if(isset($survey->event))
                <div class="mt-4">
                    <a href="#" class="text-primary-DEFAULT hover:underline text-sm">
                        Learn more about {{ $survey->event->name }}
                    </a>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Footer -->
        <div class="mt-8 text-center text-gray-500 text-xs">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
