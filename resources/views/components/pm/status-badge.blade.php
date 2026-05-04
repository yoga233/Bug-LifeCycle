@props([
    'status' => null,
    'variant' => 'outline',
    'dot' => false,
])

<x-status-badge :status="$status" :variant="$variant" :dot="$dot" {{ $attributes }}>
    {{ $slot }}
</x-status-badge>
