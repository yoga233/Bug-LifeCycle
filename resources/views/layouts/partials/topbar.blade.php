{{--
    Reusable Topbar

    Expected vars:
    - $homeRoute: string (route name)
    - $badge: string|null
    - $nav: array<array{label:string, route:string, active:string|array<string>, icon?:string}>
    - $brandLogo: string|null (URL logo brand)
    - $avatarRing: string (tailwind class)
    - $avatarGradient: string (tailwind class)
    - $showInternalLoginLink: bool (untuk public client portal)
    - $showAuthActions: bool (show Logout + avatar)
--}}

@php
    $homeRoute = $homeRoute ?? null;
    $badge = $badge ?? null;
    $nav = $nav ?? [];
    $brandLogo = $brandLogo ?? null;
    $avatarRing = $avatarRing ?? 'ring-[rgba(138,11,78,0.20)]';
    $avatarGradient = $avatarGradient ?? 'from-[#8a0b4e] to-[#b23a73]';
    $showInternalLoginLink = $showInternalLoginLink ?? false;
    $showAuthActions = $showAuthActions ?? true;

    // Optional notification icon on the right side (instead of a nav menu item)
    $notificationRoute = $notificationRoute ?? null; // route name
    $notificationReadRoute = $notificationReadRoute ?? 'programmer.notifications.read'; // route name (POST)
    $notificationCount = (int) ($notificationCount ?? 0);
    $notificationPreview = $notificationPreview ?? []; // Collection|array of Notification

    // Optional primary CTA button on the right side (filled blue)
    // Example: ['label' => 'Lapor Bug', 'route' => 'client.report', 'icon' => 'plus']
    $primaryCta = $primaryCta ?? null;

    // Optional secondary CTA button on the right side (outline/ghost)
    // Example: ['label' => 'Lacak Tiket', 'route' => 'client.tracking', 'icon' => 'search']
    $secondaryCta = $secondaryCta ?? null;

    $userMenuLabel = $userMenuLabel ?? null; // optional override for avatar dropdown label

    // Nav state classes (client-style animated underline)
    $active = 'topbar-nav-link is-active';
    $inactive = 'topbar-nav-link';
@endphp

@once
    <style>
        .topbar-shell {
            --topbar-primary-rgb: 138, 11, 78;
            --topbar-primary-2-rgb: 178, 58, 115;
            --topbar-primary: #8a0b4e;
            --topbar-primary-2: #b23a73;
            background: rgba(255, 255, 255, 0.86);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.08);
            transition: box-shadow .3s ease, background-color .3s ease, border-color .3s ease;
        }

        .topbar-shell.is-scrolled {
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 8px 28px rgba(15, 23, 42, 0.10);
            border-bottom-color: rgba(203, 213, 225, .92);
        }

        .topbar-nav-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .35rem .15rem;
            font-size: .78rem;
            font-weight: 700;
            color: #64748b;
            letter-spacing: .01em;
            transition: color .22s ease;
        }

        .topbar-nav-link::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: -0.55rem;
            height: 2px;
            border-radius: 999px;
            background: linear-gradient(90deg, rgba(var(--topbar-primary-rgb), .94), rgba(var(--topbar-primary-2-rgb), .94));
            opacity: 0;
            transform: scaleX(.2);
            transform-origin: left;
            transition: opacity .22s ease, transform .22s ease;
        }

        .topbar-nav-link:hover,
        .topbar-nav-link:focus-visible {
            color: var(--topbar-primary);
        }

        .topbar-nav-link:hover::after,
        .topbar-nav-link:focus-visible::after {
            opacity: .95;
            transform: scaleX(1);
        }

        .topbar-nav-link.is-active {
            color: var(--topbar-primary);
        }

        .topbar-nav-link.is-active::after {
            opacity: 1;
            transform: scaleX(1);
        }

        .topbar-nav-link:focus-visible {
            outline: none;
        }
    </style>
@endonce

<header
    class="topbar-shell sticky top-0 z-50"
    x-data="{ mobileOpen: false, isScrolled: window.scrollY > 50 }"
    @scroll.window="isScrolled = window.scrollY > 50"
    :class="{ 'is-scrolled': isScrolled }"
    @keydown.escape.window="mobileOpen = false"
>
    <div class="max-w-6xl mx-auto px-6">
        {{-- Desktop: grid 3 kolom (brand | nav | actions). Nav boleh kosong tanpa merusak alignment actions. --}}
        <div class="hidden md:grid grid-cols-[1fr_auto_1fr] items-center gap-4 transition-[height] duration-300" :class="isScrolled ? 'h-14' : 'h-16'">
            {{-- Brand kiri --}}
            <div class="flex items-center gap-3 justify-self-start">
                <a href="{{ $homeRoute ? route($homeRoute) : '#' }}" class="group inline-flex items-center gap-3">
                    @if($brandLogo)
                        <img
                            src="{{ $brandLogo }}"
                            alt="PRANALA BLMS"
                            class="h-9 w-auto object-contain transition-all duration-200 group-hover:opacity-90 group-hover:scale-[1.02]"
                        >
                    @else
                        {{-- Logo mark: lifecycle --}}
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[rgba(138,11,78,.10)] ring-1 ring-[rgba(138,11,78,.20)] shadow-sm transition-all duration-200 group-hover:shadow-md group-hover:ring-[rgba(138,11,78,.32)] group-hover:scale-[1.02]" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 text-[#8a0b4e]" aria-hidden="true">
                                <path d="M20 12a8 8 0 0 0-13.657-5.657" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" />
                                <path d="M4 12a8 8 0 0 0 13.657 5.657" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" />
                                <path d="M6.2 4.8v3.9h3.9" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M17.8 19.2v-3.9h-3.9" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    @endif

                    {{-- Logotype: 1 nama dominan + tagline penjelas --}}
                    <span class="leading-tight" title="PRANALA BLMS — Bug Lifecycle Management System">
                        <x-typography as="span" type="brand-wordmark" class="block text-base md:text-lg text-slate-900">
                            PRANALA <span class="text-[#8a0b4e]">BLMS</span>
                        </x-typography>
                        <x-typography as="span" type="brand-tagline" class="hidden lg:block">
                            Bug Lifecycle Management System
                        </x-typography>
                    </span>
                </a>
            </div>

            {{-- Nav tengah --}}
            <div class="justify-self-center">
                @if(!empty($nav))
                    <nav class="flex items-center gap-7 text-sm">
                        @foreach($nav as $item)
                            @php
                                $activePatterns = \Illuminate\Support\Arr::wrap($item['active'] ?? null);
                                $isActive = !empty($activePatterns) && request()->routeIs(...$activePatterns);
                            @endphp
                            <a
                                href="{{ route($item['route']) }}"
                                class="{{ $isActive ? $active : $inactive }}"
                                @if($isActive) aria-current="page" @endif
                            >
                                @if(!empty($item['icon']))
                                    @php
                                        $icon = (string) $item['icon'];
                                        // If the icon looks like a Heroicon name (letters/numbers/dash), render SVG.
                                        $isHeroIconName = (bool) preg_match('/^[a-z0-9\-]+$/i', $icon);
                                    @endphp

                                    <span class="inline-flex h-4 w-4 items-center justify-center">
                                        @if($isHeroIconName)
                                            <x-icon :name="$icon" class="h-4 w-4" />
                                        @else
                                            {{ $item['icon'] }}
                                        @endif
                                    </span>
                                @endif
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                @endif
            </div>

            {{-- Actions kanan --}}
            <div class="flex items-center gap-3 justify-self-end">
                @if($secondaryCta)
                    @php
                        $secondaryIcon = $secondaryCta['icon'] ?? null;
                        $secondaryIcon = $secondaryIcon ? (string) $secondaryIcon : null;
                    @endphp
                    <a
                        href="{{ route($secondaryCta['route']) }}"
                        class="inline-flex items-center justify-center h-9 px-4 rounded-md border border-slate-200 bg-white text-slate-700 text-xs font-semibold hover:bg-slate-50 hover:text-slate-900 shadow-sm whitespace-nowrap focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#8a0b4e] focus-visible:ring-offset-2"
                        title="{{ $secondaryCta['label'] }}"
                    >
                        @if($secondaryIcon)
                            <span class="inline-flex h-4 w-4 items-center justify-center mr-2 text-slate-500">
                                <x-icon :name="$secondaryIcon" class="h-4 w-4" />
                            </span>
                        @endif
                        {{ $secondaryCta['label'] }}
                    </a>
                @endif

                @if($primaryCta)
                    @php
                        $primaryIcon = $primaryCta['icon'] ?? 'plus';
                        $primaryIcon = $primaryIcon ? (string) $primaryIcon : null;
                    @endphp
                    <a
                        href="{{ route($primaryCta['route']) }}"
                        class="inline-flex items-center justify-center h-9 px-4 rounded-md bg-[#8a0b4e] text-white text-xs font-semibold hover:bg-[#b23a73] shadow-sm whitespace-nowrap focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#8a0b4e] focus-visible:ring-offset-2"
                        title="{{ $primaryCta['label'] }}"
                    >
                        @if($primaryIcon)
                            <span class="inline-flex h-4 w-4 items-center justify-center mr-2">
                                <x-icon :name="$primaryIcon" class="h-4 w-4" />
                            </span>
                        @endif
                        {{ $primaryCta['label'] }}
                    </a>
                @endif

                @if($showInternalLoginLink)
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-xs px-3 py-2 rounded-md text-slate-600 hover:text-slate-900 hover:bg-slate-50">
                            <span class="inline-flex h-4 w-4 items-center justify-center mr-2">
                                <x-icon name="building-office" class="h-4 w-4" />
                            </span>
                            Dashboard Internal
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center text-xs px-3 py-2 rounded-md text-slate-600 hover:text-slate-900 hover:bg-slate-50">
                            <span class="inline-flex h-4 w-4 items-center justify-center mr-2">
                                <x-icon name="lock-closed" class="h-4 w-4" />
                            </span>
                            Login Internal
                        </a>
                    @endauth
                @endif

                @if($showAuthActions)
                    @auth
                        @if($notificationRoute)
                            <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                                <button
                                    type="button"
                                    class="relative inline-flex items-center justify-center h-9 w-9 rounded-md text-slate-600 hover:text-slate-900 hover:bg-slate-50"
                                    aria-label="Notifikasi"
                                    title="Notifikasi"
                                    @click="open = !open"
                                >
                                    <x-icon name="bell" class="h-5 w-5" />
                                    @if($notificationCount > 0)
                                        <span class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] font-semibold ring-2 ring-white">
                                            {{ $notificationCount > 9 ? '9+' : $notificationCount }}
                                        </span>
                                    @endif
                                </button>

                                {{-- Dropdown preview --}}
                                <div
                                    x-cloak
                                    x-show="open"
                                    @click.outside="open = false"
                                    class="absolute right-0 mt-2 w-80 rounded-xl border border-slate-200 bg-white shadow-lg overflow-hidden"
                                >
                                    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                                        <p class="text-sm font-semibold text-slate-900">Notifikasi</p>
                                        <a href="{{ route($notificationRoute) }}" class="text-xs text-[#8a0b4e] hover:text-[#b23a73]">Lihat semua →</a>
                                    </div>

                                    <div class="max-h-80 overflow-auto">
                                        @forelse($notificationPreview as $n)
                                            <form method="POST" action="{{ route($notificationReadRoute, $n) }}" class="block">
                                                @csrf
                                                <button type="submit" class="w-full text-left px-4 py-3 hover:bg-slate-50 border-b border-slate-50">
                                                <div class="flex items-start gap-3">
                                                    <div class="mt-0.5">
                                                        <x-icon name="bell" class="h-5 w-5 text-slate-500" />
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="text-sm text-slate-900 truncate {{ !$n->is_read ? 'font-semibold' : '' }}">{{ $n->message }}</p>
                                                        <p class="text-xs text-slate-400 mt-1">{{ $n->created_at?->diffForHumans() }}</p>
                                                    </div>
                                                </div>
                                                </button>
                                            </form>
                                        @empty
                                            <div class="px-4 py-8 text-center">
                                                <p class="text-sm text-slate-500">Tidak ada notifikasi.</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Small vertical divider between notification and profile (not full height) --}}
                        @if($notificationRoute)
                            <span class="mx-1 h-6 w-px bg-slate-200" aria-hidden="true"></span>
                        @endif

                        {{-- User dropdown (nama + avatar, role pindah ke dalam dropdown) --}}
                        <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 pl-2 pr-2.5 py-1.5 rounded-lg hover:bg-slate-50 text-slate-700"
                                @click="open = !open"
                                :aria-expanded="open"
                                aria-label="User menu"
                                title="Akun"
                            >
                                <span class="hidden sm:block text-sm font-medium max-w-[160px] truncate">
                                    {{ $userMenuLabel ?? auth()->user()->name }}
                                </span>

                                @php
                                    $parts = preg_split('/\s+/', trim((string) auth()->user()->name));
                                    $first = $parts[0] ?? '';
                                    $second = $parts[1] ?? '';
                                    $initials = strtoupper(substr($first, 0, 1) . substr($second, 0, 1));
                                    if (strlen($initials) < 2) {
                                        // fallback: first two letters from name (no spaces)
                                        $compact = preg_replace('/\s+/', '', (string) auth()->user()->name);
                                        $initials = strtoupper(substr($compact, 0, 2));
                                    }
                                @endphp

                                <span
                                    class="w-8 h-8 rounded-full ring-2 {{ $avatarRing }} bg-gradient-to-br {{ $avatarGradient }} flex items-center justify-center text-white text-sm font-semibold transition duration-150 hover:brightness-90"
                                    title="Buka menu akun"
                                >
                                    {{ $initials }}
                                </span>
                            </button>

                            <div
                                x-cloak
                                x-show="open"
                                @click.outside="open = false"
                                class="absolute right-0 mt-2 w-56 rounded-xl border border-slate-200 bg-white shadow-lg overflow-hidden"
                            >
                                <div class="px-4 py-3 border-b border-slate-100">
                                    <p class="text-sm font-semibold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                                    @if($badge)
                                        <p class="mt-2">
                                            <span class="inline-flex items-center rounded-full bg-[rgba(138,11,78,.08)] text-[#8a0b4e] ring-1 ring-[rgba(138,11,78,.18)] px-2.5 py-1 text-[11px] font-semibold">
                                                {{ $badge }}
                                            </span>
                                        </p>
                                    @endif
                                </div>

                                <div class="p-2">
                                    <div class="my-1 border-t border-slate-100"></div>

                                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-slate-700 hover:bg-slate-50">
                                        <span class="inline-flex h-5 w-5 items-center justify-center text-slate-500">
                                            <x-icon name="user-circle" class="h-5 w-5" />
                                        </span>
                                        <span class="flex-1">Profil</span>
                                    </a>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-red-600 hover:bg-red-50">
                                            <span class="inline-flex h-5 w-5 items-center justify-center">
                                                <x-icon name="arrow-right-start-on-rectangle" class="h-5 w-5" />
                                            </span>
                                            <span class="flex-1 text-left">Logout</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endauth
                @endif
            </div>
        </div>

        {{-- Mobile header (flex) --}}
        <div class="md:hidden flex items-center gap-4 transition-[height] duration-300" :class="isScrolled ? 'h-14' : 'h-16'">
            <div class="flex items-center gap-2">
                <a href="{{ $homeRoute ? route($homeRoute) : '#' }}" class="group inline-flex items-center gap-3">
                    @if($brandLogo)
                        <img
                            src="{{ $brandLogo }}"
                            alt="PRANALA BLMS"
                            class="h-9 w-auto object-contain"
                        >
                    @else
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[rgba(138,11,78,.10)] ring-1 ring-[rgba(138,11,78,.20)] shadow-sm" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 text-[#8a0b4e]" aria-hidden="true">
                                <path d="M20 12a8 8 0 0 0-13.657-5.657" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" />
                                <path d="M4 12a8 8 0 0 0 13.657 5.657" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" />
                                <path d="M6.2 4.8v3.9h3.9" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M17.8 19.2v-3.9h-3.9" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    @endif

                    <span class="leading-tight">
                        <x-typography as="span" type="brand-wordmark" class="block text-sm text-slate-900">
                            PRANALA <span class="text-[#8a0b4e]">BLMS</span>
                        </x-typography>
                        <x-typography as="span" type="brand-tagline" class="block">Bug Lifecycle Management System</x-typography>
                    </span>
                </a>
            </div>

            <div class="flex items-center gap-3 ml-auto">
                @if($secondaryCta)
                    @php
                        $secondaryIcon = $secondaryCta['icon'] ?? null;
                        $secondaryIcon = $secondaryIcon ? (string) $secondaryIcon : null;
                    @endphp
                    <a
                        href="{{ route($secondaryCta['route']) }}"
                        class="inline-flex items-center justify-center h-9 px-3 rounded-md border border-slate-200 bg-white text-slate-700 text-xs font-semibold hover:bg-slate-50 hover:text-slate-900 shadow-sm whitespace-nowrap focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#8a0b4e] focus-visible:ring-offset-2"
                        title="{{ $secondaryCta['label'] }}"
                    >
                        @if($secondaryIcon)
                            <span class="inline-flex h-4 w-4 items-center justify-center mr-0 sm:mr-2 text-slate-500">
                                <x-icon :name="$secondaryIcon" class="h-4 w-4" />
                            </span>
                        @endif
                        <span class="hidden sm:inline">{{ $secondaryCta['label'] }}</span>
                    </a>
                @endif

                @if($primaryCta)
                    @php
                        $primaryIcon = $primaryCta['icon'] ?? 'plus';
                        $primaryIcon = $primaryIcon ? (string) $primaryIcon : null;
                    @endphp
                    <a
                        href="{{ route($primaryCta['route']) }}"
                        class="inline-flex items-center justify-center h-9 px-3 rounded-md bg-[#8a0b4e] text-white text-xs font-semibold hover:bg-[#b23a73] shadow-sm whitespace-nowrap focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#8a0b4e] focus-visible:ring-offset-2"
                        title="{{ $primaryCta['label'] }}"
                    >
                        @if($primaryIcon)
                            <span class="inline-flex h-4 w-4 items-center justify-center mr-0 sm:mr-2">
                                <x-icon :name="$primaryIcon" class="h-4 w-4" />
                            </span>
                        @endif
                        <span class="hidden sm:inline">{{ $primaryCta['label'] }}</span>
                    </a>
                @endif

                @if($notificationRoute && $showAuthActions)
                    @auth
                        <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                            <button
                                type="button"
                                class="relative inline-flex items-center justify-center h-9 w-9 rounded-md text-slate-600 hover:text-slate-900 hover:bg-slate-50"
                                aria-label="Notifikasi"
                                title="Notifikasi"
                                @click="open = !open"
                            >
                                <x-icon name="bell" class="h-5 w-5" />
                                @if($notificationCount > 0)
                                    <span class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] font-semibold ring-2 ring-white">
                                        {{ $notificationCount > 9 ? '9+' : $notificationCount }}
                                    </span>
                                @endif
                            </button>

                            <div
                                x-cloak
                                x-show="open"
                                @click.outside="open = false"
                                class="absolute right-0 mt-2 w-80 rounded-xl border border-slate-200 bg-white shadow-lg overflow-hidden"
                            >
                                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                                    <p class="text-sm font-semibold text-slate-900">Notifikasi</p>
                                    <a href="{{ route($notificationRoute) }}" class="text-xs text-[#8a0b4e] hover:text-[#b23a73]">Lihat semua →</a>
                                </div>

                                <div class="max-h-80 overflow-auto">
                                    @forelse($notificationPreview as $n)
                                        <form method="POST" action="{{ route($notificationReadRoute, $n) }}" class="block">
                                            @csrf
                                            <button type="submit" class="w-full text-left px-4 py-3 hover:bg-slate-50 border-b border-slate-50">
                                            <div class="flex items-start gap-3">
                                                <div class="mt-0.5">
                                                    <x-icon name="bell" class="h-5 w-5 text-slate-500" />
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-sm text-slate-900 truncate {{ !$n->is_read ? 'font-semibold' : '' }}">{{ $n->message }}</p>
                                                    <p class="text-xs text-slate-400 mt-1">{{ $n->created_at?->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                            </button>
                                        </form>
                                    @empty
                                        <div class="px-4 py-8 text-center">
                                            <p class="text-sm text-slate-500">Tidak ada notifikasi.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endauth
                @endif

                {{-- Small vertical divider between notification and profile (not full height) --}}
                @if($notificationRoute && $showAuthActions)
                    @auth
                        <span class="mx-1 h-6 w-px bg-slate-200" aria-hidden="true"></span>
                    @endauth
                @endif

                {{-- Mobile menu button (hamburger) --}}
                @if(!empty($nav))
                    <button
                        type="button"
                        class="inline-flex items-center justify-center h-9 w-9 rounded-md text-slate-600 hover:text-slate-900 hover:bg-slate-50"
                        aria-label="Open menu"
                        :aria-expanded="mobileOpen"
                        @click="mobileOpen = !mobileOpen"
                    >
                        <span x-show="!mobileOpen" x-cloak class="inline-flex">
                            <x-icon name="bars-3" class="h-5 w-5" />
                        </span>
                        <span x-show="mobileOpen" x-cloak class="inline-flex">
                            <x-icon name="x-mark" class="h-5 w-5" />
                        </span>
                    </button>
                @endif
            </div>
        </div>

        {{-- Mobile nav dropdown --}}
        @if(!empty($nav))
            <div x-cloak x-show="mobileOpen" class="md:hidden pb-4">
                <div class="mt-2 rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="p-2">
                        @foreach($nav as $item)
                            @php
                                $activePatterns = \Illuminate\Support\Arr::wrap($item['active'] ?? null);
                                $isActive = !empty($activePatterns) && request()->routeIs(...$activePatterns);
                            @endphp
                            <a
                                href="{{ route($item['route']) }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors {{ $isActive ? 'bg-[rgba(138,11,78,.08)] text-[#8a0b4e] ring-1 ring-[rgba(138,11,78,.18)] font-semibold' : 'text-slate-700 hover:bg-slate-50' }}"
                                @click="mobileOpen = false"
                                @if($isActive) aria-current="page" @endif
                            >
                                @if(!empty($item['icon']))
                                    @php
                                        $icon = (string) $item['icon'];
                                        $isHeroIconName = (bool) preg_match('/^[a-z0-9\-]+$/i', $icon);
                                    @endphp

                                    <span class="inline-flex h-5 w-5 items-center justify-center {{ $isActive ? 'text-[#8a0b4e]' : 'text-slate-500' }}">
                                        @if($isHeroIconName)
                                            <x-icon :name="$icon" class="h-5 w-5" />
                                        @else
                                            {{ $item['icon'] }}
                                        @endif
                                    </span>
                                @endif
                                <span class="flex-1">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>

                    @if($showAuthActions)
                        @auth
                            <div class="border-t border-slate-100 p-2">
                                <div class="px-3 py-2">
                                    <p class="text-sm font-semibold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                                    @if($badge)
                                        <p class="mt-2">
                                            <span class="inline-flex items-center rounded-full bg-[rgba(138,11,78,.08)] text-[#8a0b4e] ring-1 ring-[rgba(138,11,78,.18)] px-2.5 py-1 text-[11px] font-semibold">
                                                {{ $badge }}
                                            </span>
                                        </p>
                                    @endif
                                </div>

                                {{-- Divider antara blok info akun dan menu --}}
                                <div class="my-1 border-t border-slate-100"></div>

                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-slate-700 hover:bg-slate-50">
                                    <span class="inline-flex h-5 w-5 items-center justify-center text-slate-500">
                                        <x-icon name="user-circle" class="h-5 w-5" />
                                    </span>
                                    <span class="flex-1">Profil</span>
                                </a>

                                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-red-600 hover:bg-red-50"
                                    >
                                        <span class="inline-flex h-5 w-5 items-center justify-center">
                                            <x-icon name="arrow-right-start-on-rectangle" class="h-5 w-5" />
                                        </span>
                                        <span class="flex-1 text-left">Logout</span>
                                    </button>
                                </form>
                            </div>
                        @endauth
                    @endif
                </div>
            </div>
        @endif
    </div>
</header>
