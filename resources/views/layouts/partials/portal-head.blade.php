@php
    $title           = $title ?? 'PRANALA BLMS — Bug Lifecycle Management';
    $description     = $description ?? 'Report bugs directly to our engineering team...';
    $includeStyles   = $includeStyles ?? true;
    
    // Aman dari dua sisi
    $clientPortalLang = (isset($clientPortalLang) && in_array($clientPortalLang, ['en', 'id'], true))
        ? $clientPortalLang
        : 'en';
@endphp

<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}"/>
<style>
    html[data-client-lang-pending="1"] body {
        opacity: 0;
    }

    html[data-client-lang-ready="1"] body {
        opacity: 1;
        transition: opacity .14s linear;
    }
</style>
<script>
    (() => {
        const STORAGE_KEY = 'client-portal-language';
        const allowedLangs = { en: true, id: true };
        const serverLang = allowedLangs[@json($clientPortalLang)] ? @json($clientPortalLang) : 'en';
        let lang = serverLang;

        try {
            const storedLang = window.localStorage.getItem(STORAGE_KEY);
            if (allowedLangs[storedLang]) {
                lang = storedLang;
            }
        } catch (_) {
            // Ignore storage access failure (private mode / blocked storage)
        }

        document.documentElement.lang = lang;
        document.documentElement.setAttribute('data-client-lang', lang);
        document.documentElement.setAttribute('data-client-lang-pending', '1');

        window.__clientInitialLang = lang;
        window.__clientLandingLang = lang;
        window.__clientI18nReadyState = { count: 0, marked: {} };

        window.__markClientI18nReady = (name) => {
            const key = String(name || '').trim();
            if (!key) return;

            const state = window.__clientI18nReadyState || { count: 0, marked: {} };
            window.__clientI18nReadyState = state;

            if (state.marked[key]) {
                return;
            }

            state.marked[key] = true;
            state.count += 1;

            const expectedRaw = Number(document.body?.dataset?.i18nReadyExpected || 1);
            const expected = Number.isFinite(expectedRaw) && expectedRaw > 0 ? expectedRaw : 1;

            if (state.count >= expected) {
                document.documentElement.setAttribute('data-client-lang-ready', '1');
                document.documentElement.removeAttribute('data-client-lang-pending');
            }
        };

        window.setTimeout(() => {
            if (document.documentElement.getAttribute('data-client-lang-pending') !== '1') {
                return;
            }

            document.documentElement.setAttribute('data-client-lang-ready', '1');
            document.documentElement.removeAttribute('data-client-lang-pending');
        }, 2000);

        try {
            window.localStorage.setItem(STORAGE_KEY, lang);
        } catch (_) {
            // Ignore storage access failure (private mode / blocked storage)
        }
    })();
</script>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Cabinet+Grotesk:wght@400;500;600;700;800;900&family=Lora:ital,wght@0,400;0,600;1,400;1,600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>

@if ($includeStyles)
@vite('resources/css/portal-landing.css')
@endif
