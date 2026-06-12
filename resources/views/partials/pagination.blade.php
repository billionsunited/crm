@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between mt-2 mb-2 w-full">
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between w-full">
            <div>
                <p class="text-sm text-slate-500 leading-5 font-medium">
                    Showing
                    <span class="font-semibold text-slate-800">{{ $paginator->firstItem() }}</span>
                    to
                    <span class="font-semibold text-slate-800">{{ $paginator->lastItem() }}</span>
                    of
                    <span class="font-semibold text-slate-800">{{ $paginator->total() }}</span>
                    results
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex shadow-sm rounded-lg">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-slate-400 bg-white border border-slate-200 cursor-default rounded-l-lg leading-5" aria-hidden="true">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-l-lg leading-5 hover:bg-slate-50 hover:text-indigo-600 focus:z-10 focus:outline-none focus:ring-2 focus:ring-indigo-500 active:bg-slate-100 transition ease-in-out duration-150" aria-label="{{ __('pagination.previous') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-slate-700 bg-white border border-slate-200 cursor-default leading-5">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-semibold text-white bg-indigo-600 border border-indigo-600 cursor-default leading-5 z-10">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-slate-600 bg-white border border-slate-200 leading-5 hover:bg-slate-50 hover:text-indigo-600 focus:z-10 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition ease-in-out duration-150" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-3 py-2 -ml-px text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-r-lg leading-5 hover:bg-slate-50 hover:text-indigo-600 focus:z-10 focus:outline-none focus:ring-2 focus:ring-indigo-500 active:bg-slate-100 transition ease-in-out duration-150" aria-label="{{ __('pagination.next') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="relative inline-flex items-center px-3 py-2 -ml-px text-sm font-medium text-slate-400 bg-white border border-slate-200 cursor-default rounded-r-lg leading-5" aria-hidden="true">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
        
        <!-- Mobile/Small screen view -->
        <div class="flex items-center justify-end gap-3 sm:hidden w-full mt-3">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-400 bg-white border border-slate-200 cursor-default rounded-lg shadow-sm leading-5">
                    &laquo; Previous
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg shadow-sm hover:text-indigo-600 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:border-indigo-500 transition ease-in-out duration-150">
                    &laquo; Previous
                </a>
            @endif

            <span class="text-sm font-medium text-slate-500 whitespace-nowrap">
                Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg shadow-sm hover:text-indigo-600 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:border-indigo-500 transition ease-in-out duration-150">
                    Next &raquo;
                </a>
            @else
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-400 bg-white border border-slate-200 cursor-default rounded-lg shadow-sm leading-5">
                    Next &raquo;
                </span>
            @endif
        </div>
    </nav>
@endif
