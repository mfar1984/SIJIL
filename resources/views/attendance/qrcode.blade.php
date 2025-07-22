<x-app-layout>
    <x-slot name="title">Attendance QR Code</x-slot>
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
                        Attendance QR Code
                    </h1>
                    <div class="text-xs text-gray-500 mt-1">Scan this QR code to check in for attendance</div>
                </div>
                <div class="mb-6 text-center">
                    <div class="font-semibold text-base text-gray-700">{{ $attendance->event->name ?? '-' }}</div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ \Carbon\Carbon::parse($attendance->date)->format('l, d F Y') }}<br>
                        {{ substr($attendance->start_time,0,5) }} - {{ substr($attendance->end_time,0,5) }}
                    </div>
                </div>
                <div class="flex justify-center items-center">
                    {!! $qrSvg !!}
                </div>
                <div class="mt-6 text-xs text-gray-400 text-center">
                    Attendance Code: <span class="font-mono">{{ $attendance->unique_code }}</span>
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