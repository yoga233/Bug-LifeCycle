@props([
    /**
     * Severity level string (optional, used as label fallback).
     */
    'level' => null,

    /**
     * Severity model or array that may carry colors: bg_color, text_color.
     */
    'severity' => null,
])

@php
    $label = (string) ($level ?? ($severity->level ?? ($severity['level'] ?? '')));
    $levelStr = strtoupper((string) $label);

    $bgColor = $severity->bg_color ?? ($severity['bg_color'] ?? null);
    $textColor = $severity->text_color ?? ($severity['text_color'] ?? null);

    // Safe fallback palette
    $fallback = match ($levelStr) {
        'CRITICAL' => ['bg' => '#FEE2E2', 'text' => '#DC2626'],
        'MAJOR' => ['bg' => '#FEF3C7', 'text' => '#D97706'],
        'MINOR' => ['bg' => '#DBEAFE', 'text' => '#2563EB'],
        'COSMETIC' => ['bg' => '#F3F4F6', 'text' => '#6B7280'],
        default => ['bg' => '#F1F5F9', 'text' => '#475569'],
    };

    $bg = $bgColor ?: $fallback['bg'];
    $text = $textColor ?: $fallback['text'];

    $baseClass = 'inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide leading-none whitespace-nowrap';
    $style = "background-color: {$bg}; color: {$text};";
@endphp

@if($label !== '')
    <span {{ $attributes->merge(['class' => $baseClass]) }} style="{{ $style }}">
        {{ $label }}
    </span>
@endif
