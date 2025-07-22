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

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="px-2 py-1 text-gray-500 text-xs rounded-none">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="w-6 h-6 flex items-center justify-center bg-primary-light text-white rounded-full shadow-sm text-xs font-medium">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="px-2 py-1 text-gray-600 hover:text-primary-DEFAULT rounded-none text-xs font-medium" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
                
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