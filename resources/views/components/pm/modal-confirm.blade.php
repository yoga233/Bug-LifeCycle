@props([
    'name',
    'show' => false,
    'maxWidth' => 'md',
    /**
     * Theme presets:
     * - info (blue)
     * - danger (red)
     */
    'variant' => 'info',
])

@php
    $theme = match ($variant) {
        'danger' => [
            'iconBg' => 'bg-red-50',
            'iconText' => 'text-red-600',
        ],
        default => [
            'iconBg' => 'bg-blue-50',
            'iconText' => 'text-blue-600',
        ],
    };
@endphp

<x-modal :name="$name" :show="$show" :maxWidth="$maxWidth">
    <div {{ $attributes->merge(['class' => 'p-6 sm:p-7']) }}>
        <div class="flex items-start gap-4">
            <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $theme['iconBg'] }}">
                @isset($icon)
                    {{ $icon }}
                @else
                    <svg class="h-5 w-5 {{ $theme['iconText'] }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.5a.75.75 0 10-1.5 0v4.25c0 .414.336.75.75.75h2.75a.75.75 0 100-1.5h-2V6.5z" clip-rule="evenodd" />
                    </svg>
                @endisset
            </div>

            <div class="min-w-0 flex-1">
                @isset($title)
                    <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>
                @endisset

                @isset($description)
                    <p class="mt-1 text-sm text-slate-600">{{ $description }}</p>
                @endisset
            </div>
        </div>

        <div class="mt-5">
            {{ $slot }}
        </div>
    </div>
</x-modal>
