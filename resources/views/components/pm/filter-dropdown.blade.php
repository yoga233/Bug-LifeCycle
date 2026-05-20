@props([
    'name',
    'items' => [],
    'selected' => '',
    'placeholder' => 'Semua',
    'searchable' => false,
])

@php
    $items       = is_array($items) ? $items : [];
    $selected    = (string) ($selected ?? '');
    $placeholder = (string) ($placeholder ?? 'Semua');
    $searchable  = (bool) ($searchable ?? false);

    $selectedName = $placeholder;
    foreach ($items as $it) {
        $v = (string) ($it['value'] ?? '');
        if ($v !== '' && $v === $selected) {
            $selectedName = (string) ($it['label'] ?? $placeholder);
            break;
        }
    }

    if ($selected === '') {
        $selectedName = $placeholder;
    }
@endphp

<div
    x-data="{
        open: false,
        q: '',
        selectedValue: @js($selected),
        selectedLabel: @js($selectedName),
        items: @js(collect($items)->values()),
        get showSearch() {
            return @js($searchable) && (this.items?.length || 0) > 5;
        },
        get filtered() {
            const term = (this.q || '').toLowerCase().trim();
            if (!term) return this.items;
            return (this.items || []).filter(i => (i.label || '').toLowerCase().includes(term));
        },
        pick(value, label) {
            this.selectedValue = value || '';
            this.selectedLabel = label || @js($placeholder);
            this.open = false;
            this.q = '';
        },
    }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    class="relative"
>
    <input type="hidden" name="{{ $name }}" :value="selectedValue" />

    {{-- Trigger --}}
    <button
        type="button"
        @click="open = !open"
        class="inline-flex h-9 w-full items-center justify-between gap-2 rounded-xl border bg-white px-3 text-xs transition-colors duration-150 focus:outline-none focus:border-[rgba(138,11,78,0.35)] focus:ring-2 focus:ring-[rgba(138,11,78,0.10)]"
        :class="open
            ? 'border-[rgba(138,11,78,0.35)] ring-2 ring-[rgba(138,11,78,0.10)]'
            : 'border-slate-200 hover:border-[rgba(138,11,78,0.18)] hover:bg-[rgba(138,11,78,0.01)]'"
    >
        <span
            class="truncate"
            :class="selectedValue === '' ? 'text-slate-400' : 'font-medium text-slate-800'"
            x-text="selectedLabel"
        ></span>

        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
            class="h-3.5 w-3.5 shrink-0 text-slate-300 transition-transform duration-150"
            :class="open ? 'rotate-180 text-[#8a0b4e]' : ''"
            aria-hidden="true"
        >
            <path fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0l-4.25-4.51a.75.75 0 0 1 .02-1.06Z"
                  clip-rule="evenodd" />
        </svg>
    </button>

    {{-- Dropdown panel --}}
    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition duration-150 ease-out"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition duration-100 ease-in"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="absolute left-0 top-11 z-50 w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg shadow-slate-900/[0.06]"
    >
        {{-- Search field --}}
        <div x-show="showSearch" class="border-b border-slate-100 p-1.5">
            <input
                type="text"
                x-model="q"
                placeholder="Cari..."
                class="h-8 w-full rounded-lg border border-slate-200 bg-white px-2.5 text-xs text-slate-800 placeholder:text-slate-400 focus:border-[rgba(138,11,78,0.35)] focus:outline-none focus:ring-2 focus:ring-[rgba(138,11,78,0.10)]"
            />
        </div>

        {{-- Options --}}
        <div class="max-h-60 overflow-y-auto p-1">

            {{-- Placeholder / reset option --}}
            <button
                type="button"
                @click="pick('', @js($placeholder))"
                class="w-full rounded-lg px-2.5 py-2 text-left text-xs transition-colors duration-100"
                :class="selectedValue === ''
                    ? 'bg-[rgba(138,11,78,0.06)] font-semibold text-[#8a0b4e]'
                    : 'text-slate-500 hover:bg-[rgba(138,11,78,0.04)] hover:text-slate-900'"
            >
                {{ $placeholder }}
            </button>

            {{-- Items --}}
            <template x-for="item in filtered" :key="item.value">
                <button
                    type="button"
                    @click="pick(item.value, item.label)"
                    class="w-full rounded-lg px-2.5 py-2 text-left text-xs transition-colors duration-100"
                    :class="selectedValue === item.value
                        ? 'bg-[rgba(138,11,78,0.06)] font-semibold text-[#8a0b4e]'
                        : 'text-slate-700 hover:bg-[rgba(138,11,78,0.04)] hover:text-slate-900'"
                >
                    <span class="truncate" x-text="item.label"></span>
                </button>
            </template>

            {{-- Empty state --}}
            <div
                x-show="filtered.length === 0"
                class="px-2.5 py-3 text-center text-xs text-slate-400"
            >
                Tidak ditemukan
            </div>

        </div>
    </div>
</div>