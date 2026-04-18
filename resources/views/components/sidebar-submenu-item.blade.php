@props(['href' => '#', 'active' => false, 'icon' => null])

<li>
    <a href="{{ $href }}" onclick="event.stopPropagation()" {{ $attributes->merge(['class' => 'sidebar-submenu-item ' . ($active ? 'active' : '')]) }}>
        @if($icon)
            <span class="material-icons-outlined text-xs mr-3">{{ $icon }}</span>
        @endif
        <span class="text-xs">{{ $slot }}</span>
    </a>
</li> 