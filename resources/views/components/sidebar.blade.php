@props(['active' => false])

<aside class="w-64 bg-white shadow-lg relative border-r border-gray-200">
    <!-- Vertical separator line on the right -->
    <div class="absolute inset-y-0 right-0 w-px bg-gray-10"></div>
    <div class="p-4 border-b border-gray-100 relative">
        <div class="flex items-center justify-center">
            <div>
                <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height: 58px; width: auto;" onerror="this.src='https://placeholder.co/180x60?text=LOGO'">
            </div>
        </div>
    </div>
    
    <nav class="py-4 relative">
        <ul>
            {{ $slot }}
        </ul>
    </nav>
</aside> 