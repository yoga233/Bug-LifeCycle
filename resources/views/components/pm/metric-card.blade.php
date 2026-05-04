@props([
    'label',
    'value',
    'description' => null,
    'variant'     => 'neutral',
    'icon'        => null,
])

@php
    $cfg = match ($variant) {
        'action'  => [
            'value' => 'text-[#8a0b4e]',
            'icon'  => 'bg-[rgba(138,11,78,0.06)] border-[rgba(138,11,78,0.12)] text-[#8a0b4e]',
        ],
        'warning' => [
            'value' => 'text-amber-700',
            'icon'  => 'bg-amber-50 border-amber-200 text-amber-600',
        ],
        'danger'  => [
            'value' => 'text-rose-600',
            'icon'  => 'bg-rose-50 border-rose-200 text-rose-600',
        ],
        default   => [
            'value' => 'text-slate-900',
            'icon'  => 'bg-slate-50 border-slate-200 text-slate-400',
        ],
    };
@endphp

<div class="h-full rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-sm">
    <div class="flex items-start justify-between gap-4">

        <div class="min-w-0">
            <p class="font-mono text-[9px] font-medium uppercase tracking-[0.14em] text-slate-400">
                {{ $label }}
            </p>

            <p class="mt-2 text-2xl font-bold tabular-nums tracking-tight {{ $cfg['value'] }}">
                {{ is_numeric($value) ? number_format($value) : $value }}
            </p>

            @if ($description)
                <p class="mt-1 text-xs leading-relaxed text-slate-500">
                    {{ $description }}
                </p>
            @endif
        </div>

        @if ($icon)
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border transition-colors duration-200 {{ $cfg['icon'] }}">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    class="h-5 w-5"
                    aria-hidden="true"
                >
                    {!! $icon !!}
                </svg>
            </div>
        @endif

    </div>
</div>