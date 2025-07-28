<x-app-layout>
    <x-slot name="title">Event Registration QR Code</x-slot>
    <div class="min-h-screen flex flex-col justify-center items-center bg-gray-50 py-8">
        <div class="relative flex flex-col items-center">
            <!-- Minimalist Fullscreen Icon -->
            <button type="button" onclick="toggleFullScreen()" title="Full Screen" class="absolute -top-8 right-0 z-20">
                <span class="material-icons text-gray-500 text-xl hover:text-gray-700">fullscreen</span>
            </button>
            <div id="qrCard" class="bg-white rounded shadow-md border border-gray-300 p-8 flex flex-col items-center transition-all duration-200">
                <div class="mb-4 text-center">
                    <h1 class="text-lg font-bold text-gray-800 flex items-center justify-center">
                        <span class="material-icons text-primary-DEFAULT mr-2">qr_code</span>
                        Event Registration QR Code
                    </h1>
                    <div class="text-xs text-gray-500 mt-1">Scan this QR code to register for the event</div>
                </div>
                <div class="mb-6 text-center">
                    <div class="font-semibold text-base text-gray-700">{{ $event['name'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">
                        <div class="mb-1"><strong>Organizer:</strong> {{ $event['organizer'] }}</div>
                        <div class="mb-1">
                            {{ \Carbon\Carbon::parse($event['start_date'])->format('l, d F Y') }}
                            @if($event['start_date'] != $event['end_date'])
                                to {{ \Carbon\Carbon::parse($event['end_date'])->format('l, d F Y') }}
                            @endif
                        </div>
                        <div><strong>Location:</strong> {{ $event['location'] }}</div>
                    </div>
                </div>
                <div class="flex justify-center items-center">
                    {!! $qrCode !!}
                </div>
                <div class="mt-6 text-xs text-gray-400 text-center">
                    <div class="mb-2">Registration Link:</div>
                    <div class="font-mono text-xs break-all max-w-xs">{{ $registrationLink }}</div>
                </div>
                <div class="mt-4 text-xs text-gray-400 text-center">
                    Generated on: {{ now()->format('d M Y - H:i:s') }}
                </div>
            </div>
        </div>
    </div>
    <style>
    .fullscreen-center {
        min-height: 100vh !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        align-items: center !important;
        padding: 0 !important;
    }
    </style>
    <script>
    function toggleFullScreen() {
        const card = document.getElementById('qrCard');
        if (!document.fullscreenElement) {
            card.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
    }
    document.addEventListener('fullscreenchange', function() {
        const card = document.getElementById('qrCard');
        if (document.fullscreenElement === card) {
            card.classList.add('fullscreen-center');
        } else {
            card.classList.remove('fullscreen-center');
        }
    });
    </script>
</x-app-layout> 