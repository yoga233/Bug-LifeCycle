@props(['items' => []])

<nav class="mb-4 flex items-center gap-2 text-xs" aria-label="Breadcrumb">
    @foreach ($items as $index => $item)
        @if ($index > 0)
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                 class="h-3 w-3 text-slate-300" aria-hidden="true">
                <path fill-rule="evenodd"
                      d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z"
                      clip-rule="evenodd" />
            </svg>
        @endif

        @if ($loop->last)
            <span class="font-medium text-slate-600">{{ $item }}</span>
        @else
            <span class="text-slate-400">{{ $item }}</span>
        @endif
    @endforeach
</nav>