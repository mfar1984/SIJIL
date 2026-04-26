@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
        <div class="flex-1 flex items-center justify-center">
            <div class="flex items-center space-x-1">
                {{-- First Page Link --}}
                <a href="{{ $paginator->url(1) }}" class="px-2 py-1 text-gray-500 hover:text-primary-DEFAULT rounded-none text-xs {{ $paginator->onFirstPage() ? 'opacity-50 cursor-not-allowed' : '' }}" aria-label="Go to first page" {{ $paginator->onFirstPage() ? 'aria-disabled=true' : '' }}>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M15.707 15.707a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 010 1.414zm-6 0a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 1.414L5.414 10l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>
                </a>
                
                {{-- Previous Page Link --}}
                <a href="{{ $paginator->previousPageUrl() }}" class="px-2 py-1 text-gray-500 hover:text-primary-DEFAULT rounded-none text-xs mr-2 {{ $paginator->onFirstPage() ? 'opacity-50 cursor-not-allowed' : '' }}" rel="prev" aria-label="{{ __('pagination.previous') }}" {{ $paginator->onFirstPage() ? 'aria-disabled=true' : '' }}>
                     <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                </a>

                {{-- Pagination Elements with Window Logic --}}
                @php
                    $current = $paginator->currentPage();
                    $last = $paginator->lastPage();
                    $window = 2; // Pages before and after current
                    
                    // Calculate window range
                    $start = max(1, $current - $window);
                    $end = min($last, $current + $window);
                    
                    // Adjust if near boundaries
                    if ($start == 1) {
                        $end = min($last, $end + ($window - ($current - 1)));
                    }
                    if ($end == $last) {
                        $start = max(1, $start - ($window - ($last - $current)));
                    }
                @endphp

                {{-- Always show page 1 --}}
                @if ($current == 1)
                    <span class="w-6 h-6 flex items-center justify-center bg-primary-light text-white rounded-full shadow-sm text-xs font-medium">1</span>
                @else
                    <a href="{{ $paginator->url(1) }}" class="px-2 py-1 text-gray-600 hover:text-primary-DEFAULT rounded-none text-xs font-medium" aria-label="Go to page 1">
                        1
                    </a>
                @endif

                {{-- Gap before window --}}
                @if ($start > 2)
                    <span class="px-2 py-1 text-gray-500 text-xs rounded-none">...</span>
                @endif

                {{-- Window pages (skip page 1 and last page, they're handled separately) --}}
                @for ($page = max(2, $start); $page <= min($last - 1, $end); $page++)
                    @if ($page == $current)
                        <span class="w-6 h-6 flex items-center justify-center bg-primary-light text-white rounded-full shadow-sm text-xs font-medium">{{ $page }}</span>
                    @else
                        <a href="{{ $paginator->url($page) }}" class="px-2 py-1 text-gray-600 hover:text-primary-DEFAULT rounded-none text-xs font-medium" aria-label="Go to page {{ $page }}">
                            {{ $page }}
                        </a>
                    @endif
                @endfor

                {{-- Gap after window --}}
                @if ($end < $last - 1)
                    <span class="px-2 py-1 text-gray-500 text-xs rounded-none">...</span>
                @endif

                {{-- Always show last page (if more than 1 page) --}}
                @if ($last > 1)
                    @if ($current == $last)
                        <span class="w-6 h-6 flex items-center justify-center bg-primary-light text-white rounded-full shadow-sm text-xs font-medium">{{ $last }}</span>
                    @else
                        <a href="{{ $paginator->url($last) }}" class="px-2 py-1 text-gray-600 hover:text-primary-DEFAULT rounded-none text-xs font-medium" aria-label="Go to page {{ $last }}">
                            {{ $last }}
                        </a>
                    @endif
                @endif
                
                {{-- Next Page Link --}}
                <a href="{{ $paginator->nextPageUrl() }}" class="px-2 py-1 text-gray-500 hover:text-primary-DEFAULT rounded-none text-xs ml-2 {{ !$paginator->hasMorePages() ? 'opacity-50 cursor-not-allowed' : '' }}" rel="next" aria-label="{{ __('pagination.next') }}" {{ !$paginator->hasMorePages() ? 'aria-disabled=true' : '' }}>
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                </a>

                {{-- Last Page Link --}}
                <a href="{{ $paginator->url($paginator->lastPage()) }}" class="px-2 py-1 text-gray-500 hover:text-primary-DEFAULT rounded-none text-xs {{ !$paginator->hasMorePages() ? 'opacity-50 cursor-not-allowed' : '' }}" aria-label="Go to last page" {{ !$paginator->hasMorePages() ? 'aria-disabled=true' : '' }}>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414zM10 4.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L14.586 10l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                </a>
            </div>
        </div>
    </nav>
@endif 