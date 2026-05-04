@props([
    'number',
    'title',
    'titleKey',
    'description',
    'descriptionKey',
])

<article class="feat-card">
    <div class="feat-ico" aria-hidden="true">
        {{ $slot }}
    </div>
    <h3 class="feat-title" data-i18n="{{ $titleKey }}">{{ $title }}</h3>
    <p class="feat-desc" data-i18n="{{ $descriptionKey }}">{{ $description }}</p>
    <div class="feat-n" aria-hidden="true">{{ $number }}</div>
</article>