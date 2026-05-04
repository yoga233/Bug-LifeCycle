{{-- Severities --}}
<div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">

    {{-- Header --}}
    <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">
                Master Data
            </p>
            <p class="mt-1 text-sm font-medium text-slate-900">Severities</p>
            <p class="mt-1 text-sm text-slate-500">
                Contoh: Critical, Major, Minor
            </p>
        </div>

        <button
            type="button"
            class="inline-flex h-9 items-center justify-center gap-2 rounded-xl px-5 text-xs font-medium text-white transition-colors"
            style="background-color: #8a0b4e;"
            onmouseover="this.style.backgroundColor='#6d0940'"
            onmouseout="this.style.backgroundColor='#334155'"
            x-data
            x-on:click.prevent="$dispatch('open-modal', 'create-severity')"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="1.8"
                 class="h-3.5 w-3.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add Severity
        </button>
    </div>

    {{-- List --}}
    <div class="divide-y divide-slate-100">
        @forelse($severities as $s)
            <div class="group flex items-center justify-between gap-4 px-6 py-4 transition-colors duration-200 hover:bg-slate-700/[0.01]">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="truncate text-sm font-medium text-slate-900">
                            {{ $s->level }}
                        </p>

                        @if($s->text_color)
                            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600">
                                <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $s->text_color }}"></span>
                                Badge Color
                            </span>
                        @endif
                    </div>

                    <p class="mt-1.5 text-sm text-slate-500">
                        {{ $s->description }}
                    </p>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <button
                        type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-400 transition-colors hover:border-slate-700/10 hover:bg-slate-700/[0.02] hover:text-slate-700"
                        x-data
                        x-on:click.prevent="$dispatch('open-modal', 'edit-severity-{{ $s->id }}')"
                        aria-label="Edit"
                        title="Edit"
                    >
                        <x-icon name="pencil-line" class="h-4 w-4" />
                    </button>

                    <button
                        type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-rose-200 bg-white text-rose-400 transition-colors hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600"
                        x-data
                        x-on:click.prevent="$dispatch('open-modal', 'delete-severity-{{ $s->id }}')"
                        aria-label="Delete"
                        title="Delete"
                    >
                        <x-icon name="trash-2" class="h-4 w-4" />
                    </button>
                </div>
            </div>
        @empty
            <div class="px-6 py-6">
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/30 px-6 py-10 text-center">
                    <p class="text-sm font-medium text-slate-900">Belum ada severities</p>
                    <p class="mt-1 text-sm text-slate-500">
                        Tambahkan severity pertama menggunakan tombol di atas.
                    </p>
                </div>
            </div>
        @endforelse
    </div>
</div>

{{-- [Design inference: modal form mengikuti pola modal design system dengan input, action, dan swatch yang diturunkan dari token referensi.] --}}
{{-- Severities Modals --}}
<x-pm.modal-form name="create-severity" :show="($errors->any() && old('_modal') === 'create-severity')" maxWidth="2xl">
    <x-slot:title>Add Severity</x-slot:title>
    <x-slot:description>Definisikan tingkat keparahan bug untuk workflow internal.</x-slot:description>

    <form method="POST" action="{{ route('pm.master-data.severities.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="_modal" value="create-severity" />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-input-label for="severity_level" value="Level" class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400" />
                <x-text-input
                    id="severity_level"
                    name="level"
                    class="mt-1 block h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-300 focus:border-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-100"
                    required
                    placeholder="e.g. Critical"
                />
                <p class="mt-1.5 text-sm text-slate-500">Singkat dan konsisten, karena tampil sebagai label.</p>
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="severity_description" value="Description" class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400" />
                <x-text-input
                    id="severity_description"
                    name="description"
                    class="mt-1 block h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-300 focus:border-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-100"
                    required
                    placeholder="e.g. Menghentikan proses bisnis utama"
                />
            </div>

            <div class="sm:col-span-2">
                <x-input-label value="Badge Color" class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400" />
                @php
                    $severitySwatches = [
                        ['key' => 'red', 'hex' => '#DC2626', 'label' => 'Red'],
                        ['key' => 'orange', 'hex' => '#D97706', 'label' => 'Orange'],
                        ['key' => 'yellow', 'hex' => '#CA8A04', 'label' => 'Yellow'],
                        ['key' => 'blue', 'hex' => '#2563EB', 'label' => 'Blue'],
                        ['key' => 'green', 'hex' => '#16A34A', 'label' => 'Green'],
                        ['key' => 'gray', 'hex' => '#6B7280', 'label' => 'Gray'],
                        ['key' => 'purple', 'hex' => '#7C3AED', 'label' => 'Purple'],
                        ['key' => 'pink', 'hex' => '#DB2777', 'label' => 'Pink'],
                    ];

                    $selectedPreset = old('color_preset');
                @endphp

                <div class="mt-2 grid grid-cols-6 gap-2 sm:grid-cols-8">
                    @foreach($severitySwatches as $swatch)
                        <label class="relative inline-flex cursor-pointer">
                            <input
                                type="radio"
                                name="color_preset"
                                value="{{ $swatch['key'] }}"
                                class="peer sr-only"
                                @checked($selectedPreset === $swatch['key'])
                            />
                            <span
                                class="h-8 w-8 rounded-lg border border-slate-200 transition-all duration-150 peer-checked:scale-105 peer-checked:border-slate-700 peer-checked:ring-2 peer-checked:ring-slate-100 peer-focus:ring-2 peer-focus:ring-slate-100"
                                style="background-color: {{ $swatch['hex'] }}"
                                title="{{ $swatch['label'] }}"
                            ></span>
                            <span class="pointer-events-none absolute inset-0 hidden items-center justify-center text-white peer-checked:flex">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.415l-7.5 7.5a1 1 0 01-1.414 0l-3.5-3.5a1 1 0 011.414-1.415l2.793 2.794 6.793-6.794a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <x-secondary-button
                class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-100 bg-white px-4 text-xs font-medium text-slate-500 normal-case tracking-normal transition-all duration-200 hover:border-slate-700/10 hover:bg-slate-700/[0.02] hover:text-slate-700 focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0 active:outline-none"
                x-on:click="$dispatch('close-modal', 'create-severity')"
            >
                Cancel
            </x-secondary-button>
            <x-primary-button
                class="inline-flex h-9 items-center justify-center rounded-xl px-5 text-xs font-medium text-white normal-case tracking-normal"
                style="background-color: #8a0b4e;"
                onmouseover="this.style.backgroundColor='#6d0940'"
                onmouseout="this.style.backgroundColor='#334155'"
            >
                Create Severity
            </x-primary-button>
        </div>
    </form>
</x-pm.modal-form>

@foreach($severities as $s)
    <x-pm.modal-form name="edit-severity-{{ $s->id }}" :show="($errors->any() && old('_modal') === 'edit-severity-{{ $s->id }}')" maxWidth="2xl">
        <x-slot:title>Edit Severity</x-slot:title>
        <x-slot:description>Ubah informasi severity dan warna badge.</x-slot:description>

        <form method="POST" action="{{ route('pm.master-data.severities.update', $s) }}" class="space-y-5">
            @csrf
            @method('PUT')
            <input type="hidden" name="_modal" value="edit-severity-{{ $s->id }}" />

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-input-label for="severity_level_{{ $s->id }}" value="Level" class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400" />
                    <x-text-input
                        id="severity_level_{{ $s->id }}"
                        name="level"
                        class="mt-1 block h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-300 focus:border-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-100"
                        :value="$s->level"
                        required
                    />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="severity_desc_{{ $s->id }}" value="Description" class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400" />
                    <x-text-input
                        id="severity_desc_{{ $s->id }}"
                        name="description"
                        class="mt-1 block h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-300 focus:border-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-100"
                        :value="$s->description"
                        required
                    />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label value="Badge Color" class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400" />
                    @php
                        $severitySwatches = [
                            ['key' => 'red', 'hex' => '#DC2626', 'label' => 'Red'],
                            ['key' => 'orange', 'hex' => '#D97706', 'label' => 'Orange'],
                            ['key' => 'yellow', 'hex' => '#CA8A04', 'label' => 'Yellow'],
                            ['key' => 'blue', 'hex' => '#2563EB', 'label' => 'Blue'],
                            ['key' => 'green', 'hex' => '#16A34A', 'label' => 'Green'],
                            ['key' => 'gray', 'hex' => '#6B7280', 'label' => 'Gray'],
                            ['key' => 'purple', 'hex' => '#7C3AED', 'label' => 'Purple'],
                            ['key' => 'pink', 'hex' => '#DB2777', 'label' => 'Pink'],
                        ];

                        $selectedPreset = old('color_preset');
                        $currentTextColor = $s->text_color;

                        if (!$selectedPreset && $currentTextColor) {
                            $selectedPreset = match (strtoupper($currentTextColor)) {
                                '#DC2626' => 'red',
                                '#D97706' => 'orange',
                                '#CA8A04' => 'yellow',
                                '#2563EB' => 'blue',
                                '#16A34A' => 'green',
                                '#6B7280' => 'gray',
                                '#7C3AED' => 'purple',
                                '#DB2777' => 'pink',
                                default => null,
                            };
                        }
                    @endphp

                    <div class="mt-2 grid grid-cols-6 gap-2 sm:grid-cols-8">
                        @foreach($severitySwatches as $swatch)
                            <label class="relative inline-flex cursor-pointer">
                                <input
                                    type="radio"
                                    name="color_preset"
                                    value="{{ $swatch['key'] }}"
                                    class="peer sr-only"
                                    @checked($selectedPreset === $swatch['key'])
                                />
                                <span
                                    class="h-8 w-8 rounded-lg border border-slate-200 transition-all duration-150 peer-checked:scale-105 peer-checked:border-slate-700 peer-checked:ring-2 peer-checked:ring-slate-100 peer-focus:ring-2 peer-focus:ring-slate-100"
                                    style="background-color: {{ $swatch['hex'] }}"
                                    title="{{ $swatch['label'] }}"
                                ></span>
                                <span class="pointer-events-none absolute inset-0 hidden items-center justify-center text-white peer-checked:flex">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.415l-7.5 7.5a1 1 0 01-1.414 0l-3.5-3.5a1 1 0 011.414-1.415l2.793 2.794 6.793-6.794a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <x-secondary-button
                    class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-100 bg-white px-4 text-xs font-medium text-slate-500 normal-case tracking-normal transition-all duration-200 hover:border-slate-700/10 hover:bg-slate-700/[0.02] hover:text-slate-700 focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0 active:outline-none"
                    x-on:click="$dispatch('close-modal', 'edit-severity-{{ $s->id }}')"
                >
                    Cancel
                </x-secondary-button>
                <x-primary-button
                    class="inline-flex h-9 items-center justify-center rounded-xl px-5 text-xs font-medium text-white normal-case tracking-normal"
                    style="background-color: #8a0b4e;"
                    onmouseover="this.style.backgroundColor='#6d0940'"
                    onmouseout="this.style.backgroundColor='#334155'"
                >
                    Save changes
                </x-primary-button>
            </div>
        </form>
    </x-pm.modal-form>

    {{-- [Design inference: confirm modal mengikuti pola alert ringan berbasis border dan action button brand, bukan destructive solid default.] --}}
    <x-pm.modal-confirm name="delete-severity-{{ $s->id }}" :show="($errors->any() && old('_modal') === 'delete-severity-{{ $s->id }}')" maxWidth="lg" variant="danger">
        <x-slot:title>Delete Severity</x-slot:title>
        <x-slot:description>
            Hapus severity <span class="font-medium text-slate-900">{{ $s->level }}</span>?
            Aksi ini tidak dapat dibatalkan.
        </x-slot:description>

        <form method="POST" action="{{ route('pm.master-data.severities.destroy', $s) }}" class="mt-6">
            @csrf
            @method('DELETE')
            <input type="hidden" name="_modal" value="delete-severity-{{ $s->id }}" />
            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <x-secondary-button
                    class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-100 bg-white px-4 text-xs font-medium text-slate-500 transition-all duration-200 hover:border-slate-700/10 hover:bg-slate-700/[0.02] hover:text-slate-700 focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0 active:outline-none"
                    x-on:click="$dispatch('close-modal', 'delete-severity-{{ $s->id }}')"
                >
                    Cancel
                </x-secondary-button>
                <x-danger-button class="inline-flex h-9 items-center justify-center rounded-xl px-5 text-xs font-medium">
                    Delete
                </x-danger-button>
            </div>
        </form>
    </x-pm.modal-confirm>
@endforeach