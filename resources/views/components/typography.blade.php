@props([
    /**
     * Semantic type presets:
     * display, page-title, section-title, heading, subheading,
     * body, paragraph, caption, label, overline,
     * tooltip, alert-title, alert-message,
     * placeholder, link, mono,
     * brand-wordmark, brand-tagline
     */
    'as' => 'span',
    'type' => 'body',
])

@php
    $tag = in_array($as, ['span', 'p', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'label', 'small', 'strong', 'a'], true)
        ? $as
        : 'span';

    $typeClasses = [
        'display' => 'ui-text-display',
        'page-title' => 'ui-text-page-title',
        'section-title' => 'ui-text-section-title',
        'heading' => 'ui-text-heading',
        'subheading' => 'ui-text-subheading',
        'body' => 'ui-text-body',
        'paragraph' => 'ui-text-paragraph',
        'caption' => 'ui-text-caption',
        'label' => 'ui-text-label',
        'overline' => 'ui-text-overline',
        'tooltip' => 'ui-text-tooltip',
        'alert-title' => 'ui-text-alert-title',
        'alert-message' => 'ui-text-alert-message',
        'placeholder' => 'ui-text-placeholder',
        'link' => 'ui-text-link',
        'mono' => 'ui-text-mono',
        'brand-wordmark' => 'ui-brand-wordmark',
        'brand-tagline' => 'ui-brand-tagline',
    ];

    $resolvedType = $typeClasses[$type] ?? $typeClasses['body'];
@endphp

<{{ $tag }} {{ $attributes->class($resolvedType) }}>
    {{ $slot }}
</{{ $tag }}>
