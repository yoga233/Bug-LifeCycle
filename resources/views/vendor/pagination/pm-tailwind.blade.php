@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between">
        {{-- Mobile --}}
        <div class="flex w-full items-center justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center h-9 px-3 rounded-md border border-slate-200 bg-white text-sm text-slate-400 cursor-not-allowed">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                    class="inline-flex items-center h-9 px-3 rounded-md border border-slate-200 bg-white text-sm text-slate-700 hover:bg-slate-50"
                >
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a
                    href="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                    class="inline-flex items-center h-9 px-3 rounded-md border border-slate-200 bg-white text-sm text-slate-700 hover:bg-slate-50"
                >
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="inline-flex items-center h-9 px-3 rounded-md border border-slate-200 bg-white text-sm text-slate-400 cursor-not-allowed">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        {{-- Desktop --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-end">
            <span class="inline-flex items-center gap-1">
                {{-- Previous --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                        <span class="inline-flex items-center justify-center h-9 w-9 rounded-md border border-slate-200 bg-white text-slate-400 cursor-not-allowed" aria-hidden="true">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </span>
                @else
                    <a
                        href="{{ $paginator->previousPageUrl() }}"
                        rel="prev"
                        class="inline-flex items-center justify-center h-9 w-9 rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-slate-50"
                        aria-label="{{ __('pagination.previous') }}"
                    >
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @endif

                {{-- Pages (PM style): always show first & last + window 4 pages --}}
                @php
                    $current = $paginator->currentPage();
                    $last = $paginator->lastPage();
                    $window = 3;

                    $min = 2;
                    $max = $last - 1;

                    $start = max($min, $current - 1);
                    $end = $start + $window - 1;

                    if ($end > $max) {
                        $end = $max;
                        $start = max($min, $end - $window + 1);
                    }

                    if ($max < $min) {
                        $start = 2;
                        $end = 1;
                    }
                @endphp

                {{-- First --}}
                @if ($current === 1)
                    <span aria-current="page">
                        <span class="inline-flex items-center justify-center h-9 min-w-9 px-3 rounded-md text-sm font-semibold text-white cursor-default" style="background-color:#8a0b4e;border:1px solid #8a0b4e;">1</span>
                    </span>
                @else
                    <a
                        href="{{ $paginator->url(1) }}"
                        class="inline-flex items-center justify-center h-9 min-w-9 px-3 rounded-md border border-slate-200 bg-white text-sm text-slate-700 hover:bg-slate-50"
                        aria-label="{{ __('Go to page :page', ['page' => 1]) }}"
                    >1</a>
                @endif

                {{-- Left dots --}}
                @if ($start > 2)
                    <span aria-disabled="true">
                        <span class="inline-flex items-center justify-center h-9 min-w-9 px-3 rounded-md border border-slate-200 bg-white text-sm text-slate-400 cursor-default">…</span>
                    </span>
                @endif

                {{-- Window pages --}}
                @for ($page = $start; $page <= $end; $page++)
                    @if ($page === $current)
                        <span aria-current="page">
                            <span class="inline-flex items-center justify-center h-9 min-w-9 px-3 rounded-md text-sm font-semibold text-white cursor-default" style="background-color:#8a0b4e;border:1px solid #8a0b4e;">{{ $page }}</span>
                        </span>
                    @else
                        <a
                            href="{{ $paginator->url($page) }}"
                            class="inline-flex items-center justify-center h-9 min-w-9 px-3 rounded-md border border-slate-200 bg-white text-sm text-slate-700 hover:bg-slate-50"
                            aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                        >{{ $page }}</a>
                    @endif
                @endfor

                {{-- Right dots --}}
                @if ($end < ($last - 1))
                    <span aria-disabled="true">
                        <span class="inline-flex items-center justify-center h-9 min-w-9 px-3 rounded-md border border-slate-200 bg-white text-sm text-slate-400 cursor-default">…</span>
                    </span>
                @endif

                {{-- Last --}}
                @if ($last > 1)
                    @if ($current === $last)
                        <span aria-current="page">
                            <span class="inline-flex items-center justify-center h-9 min-w-9 px-3 rounded-md text-sm font-semibold text-white cursor-default" style="background-color:#8a0b4e;border:1px solid #8a0b4e;">{{ $last }}</span>
                        </span>
                    @else
                        <a
                            href="{{ $paginator->url($last) }}"
                            class="inline-flex items-center justify-center h-9 min-w-9 px-3 rounded-md border border-slate-200 bg-white text-sm text-slate-700 hover:bg-slate-50"
                            aria-label="{{ __('Go to page :page', ['page' => $last]) }}"
                        >{{ $last }}</a>
                    @endif
                @endif

                {{-- Next --}}
                @if ($paginator->hasMorePages())
                    <a
                        href="{{ $paginator->nextPageUrl() }}"
                        rel="next"
                        class="inline-flex items-center justify-center h-9 w-9 rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-slate-50"
                        aria-label="{{ __('pagination.next') }}"
                    >
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @else
                    <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                        <span class="inline-flex items-center justify-center h-9 w-9 rounded-md border border-slate-200 bg-white text-slate-400 cursor-not-allowed" aria-hidden="true">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </span>
                @endif
            </span>
        </div>
    </nav>
@endif
