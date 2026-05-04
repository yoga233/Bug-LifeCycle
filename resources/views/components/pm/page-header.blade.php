@props([
    /**
     * Breadcrumb current page label (right side).
     * Example: "Dashboard", "Manajemen Bug".
     */
    'breadcrumb' => null,

    /**
     * Main page title (H1).
     */
    'title',

    /**
     * Short description under the title.
     */
    'description' => null,
])

@php
    $crumb = $breadcrumb ?: $title;
@endphp

<div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <nav class="ui-text-caption" aria-label="Breadcrumb">
            <ol class="flex items-center gap-2">
                <li>
                    <a
                        href="{{ route('pm.dashboard') }}"
                        class="ui-text-link"
                    >
                        Project Manager
                    </a>
                </li>
                <li class="text-slate-400">/</li>
                <li class="ui-text-caption" aria-current="page">{{ $crumb }}</li>
            </ol>
        </nav>

        <h1 class="mt-1 ui-text-page-title">
            {{ $title }}
        </h1>

        @if(!empty($description))
            <p class="mt-2 ui-text-body">{{ $description }}</p>
        @endif

        {{-- Optional extra content under title/description (e.g. meta row on detail page) --}}
        @if(isset($slot) && method_exists($slot, 'isEmpty') && ! $slot->isEmpty())
            <div class="mt-2">
                {{ $slot }}
            </div>
        @endif
    </div>

    {{-- Optional actions area (right aligned) --}}
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
