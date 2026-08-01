@props(['active' => false])

<aside class="w-64 bg-white relative border-r border-gray-200">
    <div class="px-4 py-3 border-b border-gray-100 relative">
        <div class="flex items-center justify-center">
            <div>
                <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height: 52px; width: auto;" onerror="this.src='https://placeholder.co/180x60?text=LOGO'">
            </div>
        </div>
    </div>

    <nav class="py-3 relative">
        <ul>
            {{ $slot }}
        </ul>
    </nav>
</aside>
