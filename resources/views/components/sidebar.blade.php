@props(['active' => false])

<aside class="w-64 bg-white shadow-lg relative">
    <div class="absolute inset-y-0.1 right-0 w-1 bg-gradient-to-r from-transparent to-gray-200"></div>
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