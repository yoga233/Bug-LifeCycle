@props([
    /**
     * Priority level string (optional, used as label fallback).
     */
    'level' => null,

    /**
     * Priority model or array that may carry colors: bg_color, text_color.
     */
    'priority' => null,
])

@php
    $label = (string) ($level ?? ($priority->level ?? ($priority['level'] ?? '')));
    $levelStr = strtoupper((string) $label);

    $bgColor = $priority->bg_color ?? ($priority['bg_color'] ?? null);
    $textColor = $priority->text_color ?? ($priority['text_color'] ?? null);

    // Fallback palette (only used if DB colors are empty)
    $fallback = match ($levelStr) {
        'URGENT' => ['bg' => '#FEE2E2', 'text' => '#DC2626'],
        'HIGH' => ['bg' => '#FEF3C7', 'text' => '#D97706'],
        'MEDIUM' => ['bg' => '#DBEAFE', 'text' => '#2563EB'],
        'LOW' => ['bg' => '#F3F4F6', 'text' => '#6B7280'],
        default => ['bg' => '#F1F5F9', 'text' => '#475569'],
    };

    $bg = $bgColor ?: $fallback['bg'];
    $text = $textColor ?: $fallback['text'];

    $baseClass = 'inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide leading-none whitespace-nowrap';

    // Allow caller to override size via class attr if needed.
    $style = "background-color: {$bg}; color: {$text};";
@endphp

@if($label !== '')
    <span {{ $attributes->merge(['class' => $baseClass]) }} style="{{ $style }}">
        {{ $label }}
    </span>
@endif
