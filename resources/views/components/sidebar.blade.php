@props(['active' => false])

<aside class="w-64 bg-white relative border-r border-gray-200">
    <div class="px-4 py-3 border-b border-gray-100 relative">
        <div class="flex items-center justify-center">
            <div>
                {{-- Uses the Sidebar Logo from Settings > Global Config >
                     Appearance, falling back to the organisation logo and then to
                     the bundled file. This was hard-coded, so uploading a logo
                     changed nothing. --}}
                <img src="{{ \App\Support\Branding::logo('sidebar_logo') }}"
                     alt="{{ \App\Models\GlobalConfig::getConfig()->org_name ?? config('app.name') }}"
                     style="height: 52px; width: auto;"
                     onerror="this.src='{{ asset('images/logo.png') }}'">
            </div>
        </div>
    </div>

    <nav class="py-3 relative">
        <ul>
            {{ $slot }}
        </ul>
    </nav>
</aside>
