@props(['sortable' => false, 'direction' => null])

<th {{ $attributes->merge(['class' => 'px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-white']) }}>
    @if ($sortable)
        <div class="flex items-center space-x-1 cursor-pointer">
            <span>{{ $slot }}</span>
            
            @if ($direction === 'asc')
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                </svg>
            @elseif ($direction === 'desc')
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            @else
                <svg class="w-3 h-3 opacity-0 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                </svg>
            @endif
        </div>
    @else
        {{ $slot }}
    @endif
</th> 