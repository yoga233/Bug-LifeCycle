@props([
    'number',
    'total' => '03',
    'title',
    'titleKey',
    'body',
    'bodyKey',
])

<article class="prob-card">
    <div class="prob-num">{{ $number }} / {{ $total }}</div>
    <div class="prob-icon" aria-hidden="true">
        {{ $slot }}
    </div>
    <h3 class="prob-title" data-i18n="{{ $titleKey }}">{{ $title }}</h3>
    <p class="prob-body" data-i18n="{{ $bodyKey }}">{{ $body }}</p>
    <div class="prob-n" aria-hidden="true">{{ $number }}</div>
</article>