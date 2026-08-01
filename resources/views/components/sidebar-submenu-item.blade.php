@props(['href' => '#', 'active' => false, 'icon' => null])

<li>
    <a href="{{ $href }}" onclick="event.stopPropagation()" {{ $attributes->merge(['class' => 'sidebar-submenu-item ' . ($active ? 'active' : '')]) }}>
        @if($icon)
            <span class="material-icons-outlined sidebar-submenu-icon">{{ $icon }}</span>
        @endif
        <span>{{ $slot }}</span>
    </a>
</li>
