@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-wrap items-center justify-between gap-4 theme-card rounded-2xl px-5 py-3.5 no-print">
        <div class="text-xs theme-muted font-semibold">
            Showing
            <span class="font-extrabold" style="color: var(--text-main);">{{ $paginator->firstItem() }}</span>
            to
            <span class="font-extrabold" style="color: var(--text-main);">{{ $paginator->lastItem() }}</span>
            of
            <span class="font-extrabold text-indigo-500">{{ $paginator->total() }}</span>
            results
        </div>

        <div class="flex items-center gap-1.5 flex-wrap">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="px-3.5 py-2 rounded-xl text-xs font-bold theme-elevated opacity-40 cursor-not-allowed">
                    <i class="fa-solid fa-chevron-left mr-1"></i> Prev
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-3.5 py-2 rounded-xl text-xs font-bold theme-elevated hover:border-indigo-500 transition">
                    <i class="fa-solid fa-chevron-left mr-1"></i> Prev
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-3 py-2 text-xs font-bold theme-muted">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="px-3.5 py-2 rounded-xl text-xs font-extrabold bg-indigo-600 text-white">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="px-3.5 py-2 rounded-xl text-xs font-bold theme-elevated hover:border-indigo-500 transition">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="px-3.5 py-2 rounded-xl text-xs font-bold theme-elevated hover:border-indigo-500 transition">
                    Next <i class="fa-solid fa-chevron-right ml-1"></i>
                </a>
            @else
                <span class="px-3.5 py-2 rounded-xl text-xs font-bold theme-elevated opacity-40 cursor-not-allowed">
                    Next <i class="fa-solid fa-chevron-right ml-1"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
