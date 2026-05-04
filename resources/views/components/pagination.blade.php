@props([
    'paginator',
    'view' => 'vendor.pagination.pm-tailwind',
])

@php
    $paginationView = is_string($view) && $view !== '' ? $view : null;
@endphp

@if ($paginator)
    <div {{ $attributes->merge(['class' => 'mt-6']) }}>
        @if ($paginationView)
            {{ $paginator->links($paginationView) }}
        @else
            {{ $paginator->links() }}
        @endif
    </div>
@endif
