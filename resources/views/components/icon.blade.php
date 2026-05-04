@props([
    'name' => null,
])

@php
    /**
     * Canonical inline SVG icon registry for the whole app.
     *
     * NOTE:
     * - Internal and client-facing pages should use this component as single icon source.
     * - Keeps visual language consistent (same stroke, sizing defaults, fallback behavior).
     * Usage: <x-icon name="squares-2x2" class="h-4 w-4" />
     */
    $name = (string) ($name ?? '');
@endphp

<svg
    {{ $attributes->merge([
        'xmlns' => 'http://www.w3.org/2000/svg',
        'viewBox' => '0 0 24 24',
        'fill' => 'none',
        'stroke' => 'currentColor',
        'stroke-width' => '1.8',
        'class' => 'h-4 w-4',
        'aria-hidden' => 'true',
    ]) }}
>
    @switch($name)
        {{-- Common UI icons (Lucide/Heroicons-like) --}}
        @case('plus')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
            @break

        @case('check')
            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
            @break

        @case('copy')
            <rect x="9" y="9" width="13" height="13" rx="2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 5V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h1" />
            @break

        @case('plus-circle')
        @case('circle-plus')
            <circle cx="12" cy="12" r="9" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h8" />
            @break

        @case('home')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 10v11a1 1 0 0 0 1 1h4v-7h4v7h4a1 1 0 0 0 1-1V10" />
            @break

        @case('magnifying-glass')
        @case('search')
            <circle cx="11" cy="11" r="7" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35" />
            @break

        @case('arrow-left')
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m12 19-7-7 7-7" />
            @break

        @case('arrow-right')
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m12 5 7 7-7 7" />
            @break

        @case('arrow-up-right')
        @case('move-up-right')
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h8v8" />
            @break

        @case('arrow-down')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 7 7 7-7" />
            @break

        @case('bug-ant')
        @case('bug')
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 2l1.88 1.88" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.12 3.88 16 2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7.13v-1a3 3 0 0 1 6 0v1" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 20c-3.31 0-6-2.69-6-6v-3a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v3c0 3.31-2.69 6-6 6Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 20v-9" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.53 9C4.6 8.8 3 7.12 3 5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 13H2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 17H3" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.47 9C19.4 8.8 21 7.12 21 5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M22 13h-4" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 17h-3" />
            @break

        @case('bell')
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.73 21a2 2 0 0 1-3.46 0" />
            @break

        @case('alert-circle')
            <circle cx="12" cy="12" r="9" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v5" />
            <circle cx="12" cy="16" r="0.9" fill="currentColor" stroke="none" />
            @break

        @case('inbox')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 13.5V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v7.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 13.5l-2.3 4a2 2 0 0 1-1.74 1H8.04a2 2 0 0 1-1.74-1L4 13.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6" />
            @break

        @case('users')
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 20a5 5 0 0 1 10 0" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 20a5 5 0 0 1 8 0" />
            @break

        @case('check-circle')
            <circle cx="12" cy="12" r="9" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m8 12 2.5 2.5L16 9" />
            @break

        @case('lock-closed')
        @case('lock')
            <rect x="3" y="11" width="18" height="11" rx="2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 11V7a5 5 0 0 1 10 0v4" />
            @break

        @case('building-office')
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 22h18" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6h4M10 10h4M10 14h4" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 22v-4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4" />
            @break

        @case('user-circle')
            <circle cx="12" cy="12" r="10" />
            <circle cx="12" cy="10" r="3" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 20a6 6 0 0 0-12 0" />
            @break

        @case('eye')
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" />
            @break

        @case('user')
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 20.25a7.5 7.5 0 0 1 15 0" />
            @break

        @case('user-check')
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 20.25a7.5 7.5 0 0 1 15 0" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 13.5 18 15l3-3" />
            @break

        @case('folder')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75A2.25 2.25 0 0 1 6 4.5h4.19c.6 0 1.17.24 1.59.66l1.56 1.59c.42.42.99.66 1.59.66H18A2.25 2.25 0 0 1 20.25 9.75v8.25A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6.75Z" />
            @break

        @case('calendar')
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3.75v2.25" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 3.75v2.25" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 8.25h16.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v13.5A1.5 1.5 0 0 1 18.75 21.75H5.25A1.5 1.5 0 0 1 3.75 20.25V6.75A1.5 1.5 0 0 1 5.25 5.25Z" />
            @break

        @case('squares-2x2')
        @case('layout-grid')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75h6.5v6.5h-6.5v-6.5Zm0 10h6.5v6.5h-6.5v-6.5Zm10-10h6.5v6.5h-6.5v-6.5Zm0 10h6.5v6.5h-6.5v-6.5Z" />
            @break

        @case('square')
            <rect x="4" y="4" width="16" height="16" rx="2" />
            @break

        @case('activity')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h4l2.2-4.2L13 16l2.2-4H21" />
            @break

        @case('upload')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V6" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.5 9.5 3.5-3.5 3.5 3.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16.5V18a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1.5" />
            @break

        @case('mouse-pointer-2')
            <path stroke-linecap="round" stroke-linejoin="round" d="m4 4 6.8 15.9 2.5-6.6 6.6-2.5L4 4Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m13 13 4 7" />
            @break

        @case('pencil')
            <path stroke-linecap="round" stroke-linejoin="round" d="m18 2 4 4" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 21.5 7 20l13-13a2.8 2.8 0 0 0-4-4L3 16l-1.5 5.5Z" />
            @break

        @case('type')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v14" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 20h8" />
            @break

        @case('eye-off')
            <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.58 10.58a2 2 0 0 0 2.83 2.83" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.09A10.4 10.4 0 0 1 12 4.9c6 0 9.75 7.1 9.75 7.1a18.2 18.2 0 0 1-3.23 4.23" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.61 6.6A17.4 17.4 0 0 0 2.25 12s3.75 7.1 9.75 7.1a10.5 10.5 0 0 0 4.39-.98" />
            @break

        @case('undo-2')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14 4 9l5-5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 9h9a7 7 0 0 1 0 14h-2" />
            @break

        @case('redo-2')
            <path stroke-linecap="round" stroke-linejoin="round" d="m15 14 5-5-5-5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 9h-9a7 7 0 0 0 0 14h2" />
            @break

        @case('shield-check')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v6c0 4.5-3 7.8-7 9-4-1.2-7-4.5-7-9V6l7-3Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4" />
            @break

        @case('clock')
            <circle cx="12" cy="12" r="9" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" />
            @break

        @case('send')
            <path stroke-linecap="round" stroke-linejoin="round" d="M22 2 11 13" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M22 2 15 22l-4-9-9-4 20-7Z" />
            @break

        @case('tool')
        @case('wrench')
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a3.5 3.5 0 0 0 4.9 4.9l-7.9 7.9a2 2 0 1 1-2.8-2.8l7.9-7.9a3.5 3.5 0 0 1-2.1-2.1Z" />
            @break

        @case('flask-conical')
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 3h4" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 3v4l-5.6 9.7A2 2 0 0 0 6.1 20h11.8a2 2 0 0 0 1.7-3.3L14 7V3" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 14h7" />
            @break

        @case('file-text')
            <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 13H8" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 17H8" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 9H8" />
            @break

        @case('list-checks')
            <path stroke-linecap="round" stroke-linejoin="round" d="m3 7 2 2 4-4" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m3 17 2 2 4-4" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 6h10" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 12h10" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 18h10" />
            @break

        @case('ticket')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7v2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 11v2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 15v2" />
            @break

        @case('mail')
            <rect x="3" y="5" width="18" height="14" rx="2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m3 7 9 6 9-6" />
            @break

        @case('phone')
            <path stroke-linecap="round" stroke-linejoin="round" d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.08 4.18 2 2 0 0 1 4.06 2h3a2 2 0 0 1 2 1.72c.12.86.3 1.7.54 2.5a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.58-1.06a2 2 0 0 1 2.11-.45c.8.24 1.64.42 2.5.54A2 2 0 0 1 22 16.92Z" />
            @break

        @case('map-pin')
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
            <circle cx="12" cy="10" r="3" />
            @break

        @case('zap')
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 2 3 14h8l-1 8 12-14h-8l-1-6Z" />
            @break

        @case('cog-6-tooth')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.75h4.5M9.75 20.25h4.5M4.72 7.72l3.18 3.18M16.1 13.1l3.18 3.18M3.75 9.75v4.5M20.25 9.75v4.5M7.9 13.1l-3.18 3.18M19.28 7.72l-3.18 3.18" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" />
            @break

        @case('presentation-chart-line')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18v12H3V3Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v6" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.5 11l3-3 2 2 4-4 2 2" />
            @break

        @case('arrow-right-start-on-rectangle')
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5A2.25 2.25 0 0 0 6.75 19.5h3.75" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 16.5 17.25 12 13.5 7.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 12H9" />
            @break

        @case('bars-3')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            @break

        @case('x-mark')
        @case('x')
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
            @break

        @case('chevron-down')
            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
            @break

        {{-- Lucide Icons --}}
        @case('pencil-line')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m15 5 3 3" />
            @break

        @case('user-x')
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M2 21a8 8 0 0 1 13.8-5.3" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m22 8-4 4" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m18 8 4 4" />
            @break

        @case('trash-2')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M14 11v6" />
            @break

        @case('layers')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12.83 2.18a2 2 0 0 0-1.66 0l-8 3.2a1 1 0 0 0 0 1.84l8 3.2a2 2 0 0 0 1.66 0l8-3.2a1 1 0 0 0 0-1.84l-8-3.2Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m22 12-8.34 3.34a2 2 0 0 1-1.32 0L4 12" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m22 17-8.34 3.34a2 2 0 0 1-1.32 0L4 17" />
            @break

        @default
            {{-- Fallback: simple circle to avoid breaking layout when an unknown icon name is used --}}
            <circle cx="12" cy="12" r="9" />
    @endswitch
</svg>
