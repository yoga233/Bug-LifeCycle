@props(['ariaLabel' => 'Informasi bantuan'])

<span
    class="relative inline-flex"
    x-data="{ open: false }"
    @mouseenter="open = true"
    @mouseleave="open = false"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
>
    <button
        type="button"
        @click="open = !open"
        @focus="open = true"
        :aria-expanded="open.toString()"
        aria-label="{{ $ariaLabel }}"
        class="inline-flex h-[18px] w-[18px] items-center justify-center rounded-full border border-slate-200 bg-white text-[10px] font-medium text-slate-400 transition-all duration-150 hover:border-[rgba(138,11,78,0.24)] hover:text-[#8a0b4e] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.20)] focus-visible:ring-offset-1"
    >
        ?
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition duration-150 ease-out"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition duration-100 ease-in"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute left-0 top-6 z-20 w-72 rounded-xl border border-slate-200 bg-white p-4 text-xs leading-relaxed text-slate-500 shadow-lg shadow-slate-900/[0.06]"
        role="tooltip"
    >
        {{ $slot }}
    </div>
</span>