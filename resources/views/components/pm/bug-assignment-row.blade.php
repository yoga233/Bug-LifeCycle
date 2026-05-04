@props(['bug', 'programmers'])

@php
    $hasPriority = (bool) $bug->priority_id;
    $reportedAt  = $bug->created_at?->locale('id')->diffForHumans();
    $reportedAt  = $reportedAt ? str_replace(' yang lalu', ' lalu', $reportedAt) : null;
@endphp

<div class="group px-5 py-5 transition-colors duration-150 hover:bg-[rgba(138,11,78,0.022)]">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        {{-- ══ Informasi Tiket ══ --}}
        <div class="min-w-0 flex-1">

            {{-- Baris 1: ID + Prioritas + Proyek --}}
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="font-mono text-[10px] font-medium tracking-[0.06em] text-slate-400">
                    Tiket #{{ $bug->id }}
                </span>

                @if ($bug->priority)
                    <x-priority-badge :priority="$bug->priority" />
                @endif

                @if ($bug->project?->name)
                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-medium text-slate-500">
                        {{ $bug->project->name }}
                    </span>
                @endif
            </div>

            {{-- Baris 2: Judul --}}
            <h4
                class="mt-2.5 truncate text-sm font-medium leading-snug text-slate-800"
                title="{{ $bug->title }}"
            >
                {{ $bug->title }}
            </h4>

            {{-- Baris 3: Pelapor + Waktu --}}
            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                <span class="inline-flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.75"
                         class="h-3.5 w-3.5 text-slate-400" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    <span>
                        Dilaporkan oleh
                        <span class="font-medium text-slate-700">{{ $bug->guest_name }}</span>
                    </span>
                </span>

                @if ($reportedAt)
                    <span class="inline-flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.75"
                             class="h-3.5 w-3.5 text-slate-400" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span>{{ $reportedAt }}</span>
                    </span>
                @endif
            </div>

        </div>

        {{-- ══ Aksi ══ --}}
        <div class="flex shrink-0 items-center gap-2">

            @if ($hasPriority)

                <form
                    method="POST"
                    action="{{ route('pm.issues.assign', $bug) }}"
                    class="flex items-center gap-2"
                    x-data="{
                        assigneeId: '',
                        assigneeName: '',
                        openAssignee: false,
                        submitting: false,
                        dropUp: false,
                        dropdownMaxHeight: 208,

                        toggleAssignee() {
                            this.openAssignee = !this.openAssignee;
                            if (this.openAssignee) {
                                this.$nextTick(() => this.updateDropdownPosition());
                            }
                        },

                        closeAssignee() {
                            this.openAssignee = false;
                        },

                        updateDropdownPosition() {
                            const trigger = this.$refs.assigneeTrigger?.getBoundingClientRect();
                            const menu = this.$refs.assigneeMenu;
                            if (!trigger || !menu) return;

                            const gap = 8;
                            const desiredHeight = Math.min(menu.scrollHeight || 208, 208);
                            const spaceBelow = window.innerHeight - trigger.bottom - gap;
                            const spaceAbove = trigger.top - gap;

                            this.dropUp = spaceBelow < desiredHeight && spaceAbove > spaceBelow;
                            const availableSpace = this.dropUp ? spaceAbove : spaceBelow;
                            this.dropdownMaxHeight = Math.max(120, Math.min(208, availableSpace - 8));
                        }
                    }"
                    @resize.window="openAssignee && updateDropdownPosition()"
                    @scroll.window="openAssignee && updateDropdownPosition()"
                    @submit="
                        if (submitting) { $event.preventDefault(); return; }
                        if (!assigneeId) { $event.preventDefault(); return; }
                        if (!confirm('Tugaskan tiket #{{ $bug->id }} ke ' + (assigneeName || 'programmer terpilih') + '?')) {
                            $event.preventDefault(); return;
                        }
                        submitting = true;
                    "
                >
                    @csrf
                    <input type="hidden" name="assignee_id" :value="assigneeId">

                    <div
                        class="relative"
                        @click.outside="closeAssignee()"
                        @keydown.escape.window="closeAssignee()"
                    >
                        <button
                            type="button"
                            x-ref="assigneeTrigger"
                            @click="toggleAssignee()"
                            class="inline-flex h-8 w-44 items-center justify-between gap-1.5 rounded-lg border bg-white px-2.5 text-xs transition-all duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.25)] focus-visible:ring-offset-1"
                            :class="openAssignee
                                ? 'border-[rgba(138,11,78,0.35)] ring-2 ring-[rgba(138,11,78,0.10)]'
                                : 'border-slate-200 hover:border-[rgba(138,11,78,0.20)] hover:bg-[rgba(138,11,78,0.02)]'"
                        >
                            <span
                                class="truncate"
                                :class="assigneeName ? 'font-medium text-slate-700' : 'text-slate-400'"
                                x-text="assigneeName || 'Pilih programmer…'"
                            ></span>

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-3.5 w-3.5 shrink-0 text-slate-400 transition-transform duration-150"
                                :class="openAssignee ? 'rotate-180 text-[#8a0b4e]' : ''"
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div
                            x-cloak
                            x-ref="assigneeMenu"
                            x-show="openAssignee"
                            x-transition:enter="transition duration-150 ease-out"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition duration-100 ease-in"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            :class="dropUp
                                ? 'absolute right-0 bottom-full mb-1.5 origin-bottom-right'
                                : 'absolute right-0 top-full mt-1.5 origin-top-right'"
                            class="z-[60] w-full overflow-hidden rounded-xl border border-slate-200 bg-white p-1 shadow-lg shadow-slate-900/[0.06]"
                        >
                            <div class="overflow-y-auto" :style="`max-height: ${dropdownMaxHeight}px`">
                                @foreach ($programmers as $dev)
                                    <button
                                        type="button"
                                        @click="
                                            assigneeId = @js((string) $dev->id);
                                            assigneeName = @js((string) $dev->name);
                                            openAssignee = false;
                                        "
                                        class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-left text-xs transition-colors duration-100"
                                        :class="assigneeId === @js((string) $dev->id)
                                            ? 'bg-[rgba(138,11,78,0.06)] font-semibold text-[#8a0b4e]'
                                            : 'text-slate-600 hover:bg-[rgba(138,11,78,0.04)] hover:text-slate-800'"
                                    >
                                        <span
                                            class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[9px] font-semibold uppercase"
                                            :class="assigneeId === @js((string) $dev->id)
                                                ? 'bg-[rgba(138,11,78,0.10)] text-[#8a0b4e]'
                                                : 'bg-slate-100 text-slate-400'"
                                            aria-hidden="true"
                                        >
                                            {{ mb_substr($dev->name, 0, 1) }}
                                        </span>
                                        {{ $dev->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="!assigneeId || submitting"
                        class="inline-flex h-8 items-center justify-center rounded-lg bg-[#8a0b4e] px-4 text-xs font-semibold text-white transition-all duration-150 hover:bg-[#6d0940] disabled:cursor-not-allowed disabled:opacity-40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.30)] focus-visible:ring-offset-1"
                    >
                        <span x-show="!submitting">Tugaskan</span>
                        <span x-show="submitting" x-cloak class="inline-flex items-center gap-1.5">
                            <svg class="h-3 w-3 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Menugaskan…
                        </span>
                    </button>
                </form>

                <a
                    href="{{ route('pm.issues.show', $bug) }}?return={{ urlencode(url()->current()) }}"
                    class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-xs font-medium text-slate-600 transition-all duration-150 hover:border-[rgba(138,11,78,0.18)] hover:text-[#8a0b4e] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.25)] focus-visible:ring-offset-1"
                >
                    Lihat Detail
                </a>

            @else

                <a
                    href="{{ route('pm.issues.show', $bug) }}?return={{ urlencode(url()->current()) }}"
                    class="inline-flex h-8 items-center justify-center rounded-lg bg-[#8a0b4e] px-4 text-xs font-semibold text-white transition-all duration-150 hover:bg-[#6d0940] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.30)] focus-visible:ring-offset-1"
                >
                    Tentukan Prioritas
                </a>

            @endif

        </div>

    </div>
</div>