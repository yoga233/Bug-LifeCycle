{{-- Priorities --}}
<div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">

    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
        <div>
            <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">
                Master Data
            </p>
            <p class="mt-1 text-sm font-medium text-slate-900">Priorities</p>
            <p class="mt-1 text-xs text-slate-500">
                Contoh: High / Medium / Low (dengan target SLA jam)
            </p>
        </div>

        <button
            type="button"
            class="inline-flex h-9 items-center justify-center gap-2 rounded-xl px-4 text-xs font-medium text-white transition-colors"
        style="background-color: #8a0b4e;"
        onmouseover="this.style.backgroundColor='#6d0940'"
        onmouseout="this.style.backgroundColor='#8a0b4e'"
            x-data
            x-on:click.prevent="$dispatch('open-modal', 'create-priority')"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2"
                 class="h-3.5 w-3.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add Priority
        </button>
    </div>

    {{-- List --}}
    <div class="divide-y divide-slate-100">
        @forelse($priorities as $p)
            @php
                $slaHours   = (int) $p->sla_hours;
                $slaDisplay = $slaHours <= 72
                    ? $slaHours . ' Jam'
                    : ceil($slaHours / 24) . ' Hari';
            @endphp

            <div class="flex items-center justify-between gap-4 px-6 py-4">
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-slate-900">
                        {{ $p->level }}
                    </p>
                    <p class="mt-0.5 text-xs text-slate-500">
                        Target SLA: {{ $slaDisplay }}
                    </p>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <button
                        type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-[#8a0b4e] transition-colors hover:border-[#8a0b4e]/20 hover:bg-[#f5e8ef] hover:text-[#6d0940]"
                        x-data
                        x-on:click.prevent="$dispatch('open-modal', 'edit-priority-{{ $p->id }}')"
                        aria-label="Edit"
                        title="Edit"
                    >
                        <x-icon name="pencil-line" class="h-4 w-4" />
                    </button>

                    <button
                        type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-rose-200 bg-white text-rose-400 transition-colors hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600"
                        x-data
                        x-on:click.prevent="$dispatch('open-modal', 'delete-priority-{{ $p->id }}')"
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
                    <p class="text-sm font-medium text-slate-900">Belum ada priorities</p>
                    <p class="mt-1 text-sm text-slate-500">
                        Tambahkan priority pertama menggunakan tombol di atas.
                    </p>
                </div>
            </div>
        @endforelse
    </div>
</div>

{{-- Priorities Modals --}}
<x-pm.modal-form name="create-priority" :show="($errors->any() && old('_modal') === 'create-priority')" maxWidth="2xl">
    <x-slot:title>Add Priority</x-slot:title>
    <x-slot:description>Tentukan prioritas pengerjaan (SLA) beserta warna badge.</x-slot:description>

    <form method="POST" action="{{ route('pm.master-data.priorities.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="_modal" value="create-priority" />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-input-label for="priority_level" value="Level" />
                <x-text-input id="priority_level" name="level" class="mt-1 block w-full" required placeholder="e.g. High" />
                <p class="mt-1 text-xs text-slate-500">Contoh: High, Medium, Low.</p>
            </div>

            <div class="sm:col-span-2">
                @php
                    $oldCreateSlaHours = old('sla_hours');
                    $createSlaHours    = $oldCreateSlaHours !== null ? (int) $oldCreateSlaHours : 24;
                    $createSlaUnit     = $createSlaHours > 72 && $createSlaHours % 24 === 0 ? 'day' : 'hour';
                    $createSlaValue    = $createSlaUnit === 'day' ? (int) ($createSlaHours / 24) : $createSlaHours;
                @endphp

                <x-input-label for="priority_sla_value" value="Target SLA" />
                <div
                    class="mt-2 rounded-xl border border-slate-200 bg-slate-50/70 p-3 sm:p-4"
                    x-data="{ slaValue: {{ $createSlaValue }}, slaUnit: '{{ $createSlaUnit }}' }"
                >
                    <input type="hidden" name="sla_hours" :value="slaUnit === 'day' ? (Number(slaValue || 0) * 24) : Number(slaValue || 0)">

                    <div class="grid grid-cols-1 items-center gap-3 sm:grid-cols-[1fr_auto]">
                        <x-text-input
                            id="priority_sla_value"
                            type="number"
                            min="1"
                            step="1"
                            class="block w-full"
                            required
                            x-model.number="slaValue"
                            placeholder="Masukkan nilai SLA"
                        />

                        {{-- [Design inference: Tab toggle — rounded-xl outer, rounded-lg inner buttons, active #8a0b4e] --}}
                        <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1">
                            <button
                                type="button"
                                class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors duration-150"
                                :class="slaUnit === 'hour' ? 'text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700'"
                                :style="slaUnit === 'hour' ? 'background-color:#8a0b4e' : ''"
                                @click="slaUnit = 'hour'"
                            >Jam</button>
                            <button
                                type="button"
                                class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors duration-150"
                                :class="slaUnit === 'day' ? 'text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700'"
                                :style="slaUnit === 'day' ? 'background-color:#8a0b4e' : ''"
                                @click="slaUnit = 'day'"
                            >Hari</button>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                        <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-100 px-2.5 py-1 text-slate-700">
                            Disimpan: <span class="ml-1 font-semibold" x-text="(slaUnit === 'day' ? (Number(slaValue || 0) * 24) : Number(slaValue || 0)) + ' Jam'"></span>
                        </span>
                        <span class="text-slate-500">Jika lebih dari 72 jam, sistem otomatis menampilkan dalam Hari.</span>
                    </div>
                </div>
            </div>

            <div class="sm:col-span-2">
                <x-input-label value="Badge Color" />
                @php
                    $prioritySwatches = [
                        ['key' => 'red',    'hex' => '#DC2626', 'label' => 'Red'],
                        ['key' => 'orange', 'hex' => '#D97706', 'label' => 'Orange'],
                        ['key' => 'yellow', 'hex' => '#CA8A04', 'label' => 'Yellow'],
                        ['key' => 'blue',   'hex' => '#2563EB', 'label' => 'Blue'],
                        ['key' => 'green',  'hex' => '#16A34A', 'label' => 'Green'],
                        ['key' => 'gray',   'hex' => '#6B7280', 'label' => 'Gray'],
                        ['key' => 'purple', 'hex' => '#7C3AED', 'label' => 'Purple'],
                        ['key' => 'pink',   'hex' => '#DB2777', 'label' => 'Pink'],
                    ];
                    $selectedPreset = old('color_preset');
                @endphp

                <div class="mt-2 grid grid-cols-6 gap-2 sm:grid-cols-8">
                    @foreach($prioritySwatches as $swatch)
                        <label class="relative inline-flex cursor-pointer">
                            <input
                                type="radio"
                                name="color_preset"
                                value="{{ $swatch['key'] }}"
                                class="sr-only peer"
                                @checked($selectedPreset === $swatch['key'])
                            />
                            <span
                                class="h-7 w-7 rounded-lg ring-1 ring-slate-200 peer-checked:ring-2 peer-checked:ring-slate-700 peer-focus:ring-2 peer-focus:ring-slate-700"
                                style="background-color: {{ $swatch['hex'] }}"
                                title="{{ $swatch['label'] }}"
                            ></span>
                            <span class="pointer-events-none absolute inset-0 hidden items-center justify-center text-white peer-checked:flex">
                                <svg class="h-4 w-4 drop-shadow" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.415l-7.5 7.5a1 1 0 01-1.414 0l-3.5-3.5a1 1 0 011.414-1.415l2.793 2.794 6.793-6.794a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-secondary-button class="justify-center normal-case tracking-normal" x-on:click="$dispatch('close-modal', 'create-priority')">Cancel</x-secondary-button>
            <x-primary-button
                class="justify-center normal-case tracking-normal"
                style="background-color:#8a0b4e;"
                onmouseover="this.style.backgroundColor='#6d0940'"
                onmouseout="this.style.backgroundColor='#8a0b4e'"
            >
                Create Priority
            </x-primary-button>
        </div>
    </form>
</x-pm.modal-form>

@foreach($priorities as $p)
    <x-pm.modal-form name="edit-priority-{{ $p->id }}" :show="($errors->any() && old('_modal') === 'edit-priority-{{ $p->id }}')" maxWidth="2xl">
        <x-slot:title>Edit Priority</x-slot:title>
        <x-slot:description>Ubah level, SLA, dan warna badge.</x-slot:description>

        <form method="POST" action="{{ route('pm.master-data.priorities.update', $p) }}" class="space-y-5">
            @csrf
            @method('PUT')
            <input type="hidden" name="_modal" value="edit-priority-{{ $p->id }}" />

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-input-label for="priority_level_{{ $p->id }}" value="Level" />
                    <x-text-input id="priority_level_{{ $p->id }}" name="level" class="mt-1 block w-full" :value="$p->level" required />
                </div>

                <div class="sm:col-span-2">
                    @php
                        $isCurrentEditModal = old('_modal') === 'edit-priority-' . $p->id;
                        $editSlaHours       = $isCurrentEditModal ? (int) old('sla_hours', $p->sla_hours) : (int) $p->sla_hours;
                        $editSlaUnit        = $editSlaHours > 72 && $editSlaHours % 24 === 0 ? 'day' : 'hour';
                        $editSlaValue       = $editSlaUnit === 'day' ? (int) ($editSlaHours / 24) : $editSlaHours;
                    @endphp

                    <x-input-label for="priority_sla_value_{{ $p->id }}" value="Target SLA" />
                    <div
                        class="mt-2 rounded-xl border border-slate-200 bg-slate-50/70 p-3 sm:p-4"
                        x-data="{ slaValue: {{ $editSlaValue }}, slaUnit: '{{ $editSlaUnit }}' }"
                    >
                        <input type="hidden" name="sla_hours" :value="slaUnit === 'day' ? (Number(slaValue || 0) * 24) : Number(slaValue || 0)">

                        <div class="grid grid-cols-1 items-center gap-3 sm:grid-cols-[1fr_auto]">
                            <x-text-input
                                id="priority_sla_value_{{ $p->id }}"
                                type="number"
                                min="1"
                                step="1"
                                class="block w-full"
                                required
                                x-model.number="slaValue"
                                placeholder="Masukkan nilai SLA"
                            />

                            {{-- [Design inference: Tab toggle — rounded-xl outer, rounded-lg inner buttons, active #8a0b4e] --}}
                            <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1">
                                <button
                                    type="button"
                                    class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors duration-150"
                                    :class="slaUnit === 'hour' ? 'text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700'"
                                    :style="slaUnit === 'hour' ? 'background-color:#8a0b4e' : ''"
                                    @click="slaUnit = 'hour'"
                                >Jam</button>
                                <button
                                    type="button"
                                    class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors duration-150"
                                    :class="slaUnit === 'day' ? 'text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700'"
                                    :style="slaUnit === 'day' ? 'background-color:#8a0b4e' : ''"
                                    @click="slaUnit = 'day'"
                                >Hari</button>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                            <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-100 px-2.5 py-1 text-slate-700">
                                Disimpan: <span class="ml-1 font-semibold" x-text="(slaUnit === 'day' ? (Number(slaValue || 0) * 24) : Number(slaValue || 0)) + ' Jam'"></span>
                            </span>
                            <span class="text-slate-500">Jika lebih dari 72 jam, sistem otomatis menampilkan dalam Hari.</span>
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <x-input-label value="Badge Color" />
                    @php
                        $prioritySwatches = [
                            ['key' => 'red',    'hex' => '#DC2626', 'label' => 'Red'],
                            ['key' => 'orange', 'hex' => '#D97706', 'label' => 'Orange'],
                            ['key' => 'yellow', 'hex' => '#CA8A04', 'label' => 'Yellow'],
                            ['key' => 'blue',   'hex' => '#2563EB', 'label' => 'Blue'],
                            ['key' => 'green',  'hex' => '#16A34A', 'label' => 'Green'],
                            ['key' => 'gray',   'hex' => '#6B7280', 'label' => 'Gray'],
                            ['key' => 'purple', 'hex' => '#7C3AED', 'label' => 'Purple'],
                            ['key' => 'pink',   'hex' => '#DB2777', 'label' => 'Pink'],
                        ];

                        $selectedPreset   = old('color_preset');
                        $currentTextColor = $p->text_color;

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
                                default   => null,
                            };
                        }
                    @endphp

                    <div class="mt-2 grid grid-cols-6 gap-2 sm:grid-cols-8">
                        @foreach($prioritySwatches as $swatch)
                            <label class="relative inline-flex cursor-pointer">
                                <input
                                    type="radio"
                                    name="color_preset"
                                    value="{{ $swatch['key'] }}"
                                    class="sr-only peer"
                                    @checked($selectedPreset === $swatch['key'])
                                />
                                <span
                                    class="h-7 w-7 rounded-lg ring-1 ring-slate-200 peer-checked:ring-2 peer-checked:ring-slate-700 peer-focus:ring-2 peer-focus:ring-slate-700"
                                    style="background-color: {{ $swatch['hex'] }}"
                                    title="{{ $swatch['label'] }}"
                                ></span>
                                <span class="pointer-events-none absolute inset-0 hidden items-center justify-center text-white peer-checked:flex">
                                    <svg class="h-4 w-4 drop-shadow" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.415l-7.5 7.5a1 1 0 01-1.414 0l-3.5-3.5a1 1 0 011.414-1.415l2.793 2.794 6.793-6.794a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-secondary-button class="justify-center normal-case tracking-normal" x-on:click="$dispatch('close-modal', 'edit-priority-{{ $p->id }}')">Cancel</x-secondary-button>
                <x-primary-button
                    class="justify-center normal-case tracking-normal"
                    style="background-color:#8a0b4e;"
                    onmouseover="this.style.backgroundColor='#6d0940'"
                    onmouseout="this.style.backgroundColor='#8a0b4e'"
                >
                    Save Changes
                </x-primary-button>
            </div>
        </form>
    </x-pm.modal-form>

    <x-pm.modal-confirm name="delete-priority-{{ $p->id }}" :show="($errors->any() && old('_modal') === 'delete-priority-{{ $p->id }}')" maxWidth="lg" variant="danger">
        <x-slot:title>Delete Priority</x-slot:title>
        <x-slot:description>
            Hapus priority <span class="font-medium">{{ $p->level }}</span>?
            Aksi ini tidak dapat dibatalkan.
        </x-slot:description>

        <form method="POST" action="{{ route('pm.master-data.priorities.destroy', $p) }}" class="mt-6">
            @csrf
            @method('DELETE')
            <input type="hidden" name="_modal" value="delete-priority-{{ $p->id }}" />
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-secondary-button class="justify-center" x-on:click="$dispatch('close-modal', 'delete-priority-{{ $p->id }}')">Cancel</x-secondary-button>
                <x-danger-button class="justify-center">Delete</x-danger-button>
            </div>
        </form>
    </x-pm.modal-confirm>
@endforeach