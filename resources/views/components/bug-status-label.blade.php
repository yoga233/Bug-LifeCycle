@props([
    'status' => null,
])

{{-- Backward-compatible alias: render text label exactly like before, sourced from unified status component. --}}
<x-status-badge :status="$status" variant="text" {{ $attributes }}>
    {{ $slot }}
</x-status-badge>
