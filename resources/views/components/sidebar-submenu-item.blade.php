@props(['href' => '#', 'active' => false, 'icon' => null])

<li>
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'sidebar-submenu-item ' . ($active ? 'active' : '')]) }}>
        @if($icon)
            <span class="material-icons text-xs mr-3">{{ $icon }}</span>
        @endif
        <span class="text-xs">{{ $slot }}</span>
    </a>
</li> 