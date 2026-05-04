<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @props([
        /**
         * Wrap guest pages in a centered card.
         * - Login page uses split panel and provides its own card, so it sets :card="false".
         */
        'card' => true,
        'maxWidth' => 'md',
    ])

    @php
        $maxWidthClass = match ((string) $maxWidth) {
            'sm' => 'sm:max-w-sm',
            'md' => 'sm:max-w-md',
            'lg' => 'sm:max-w-lg',
            'xl' => 'sm:max-w-xl',
            '2xl' => 'sm:max-w-2xl',
            'none', 'full' => '',
            default => 'sm:max-w-md',
        };
    @endphp

    <head>
        @include('layouts.partials.head', ['title' => config('app.name', 'PRANALA BLMS')])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div
            @class([
                'min-h-screen relative overflow-hidden flex items-center justify-center px-4 py-8',
                // Default guest pages (forgot password, reset password, etc.) keep a calm neutral background.
                'bg-slate-100' => $card,
                // Login page uses :card="false" (split panel), so we give it a more premium background.
                'bg-gradient-to-br from-slate-50 via-slate-100 to-slate-200' => ! $card,
            ])
        >
            @if (! $card)
                {{-- Background ornaments (subtle, clean, non-distracting) --}}
                <div class="pointer-events-none absolute inset-0 z-0" aria-hidden="true">
                    <div class="absolute -top-40 -left-40 h-[28rem] w-[28rem] rounded-full bg-[#8a0b4e]/15 blur-3xl"></div>
                    <div class="absolute -bottom-40 -right-40 h-[28rem] w-[28rem] rounded-full bg-[#b01567]/15 blur-3xl"></div>

                    {{-- Subtle dot grid pattern --}}
                    <div class="absolute inset-0 opacity-[0.22] bg-[radial-gradient(circle_at_1px_1px,rgba(15,23,42,0.08)_1px,transparent_0)] [background-size:24px_24px]"></div>
                </div>
            @endif

            @if ($card)
                <div class="relative z-10 w-full {{ $maxWidthClass }} overflow-hidden rounded-2xl bg-white px-6 py-5 shadow-sm ring-1 ring-slate-200">
                    {{ $slot }}
                </div>
            @else
                {{-- Keep the split-login panel centered while preserving z-index above ornaments --}}
                <div class="relative z-10 w-full flex justify-center">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </body>
</html>
