@props([
    'name',
    'class' => 'h-5 w-5',
    'strokeWidth' => 1.8,
])

{{--
    Backward-compatible alias.
    Canonical icon source is now <x-icon />, including for client landing page.
--}}
<x-icon
    :name="$name"
    :class="$class"
    :stroke-width="$strokeWidth"
    {{ $attributes }}
/>
