@props(['bug', 'programmers'])

@php
    $hasPriority = (bool) $bug->priority_id;
    $reportedAt = $bug->created_at?->locale('id')->diffForHumans();
    $reportedAt = $reportedAt ? str_replace(' yang lalu', ' lalu', $reportedAt) : null;
@endphp

<div class="group px-5 py-4 transition-colors duration-150 hover:bg-[#8a0b4e]/[0.015]">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        {{-- ══ Bug Info ══ --}}
        <div class="min-w-0 flex-1">

            {{-- Row 1: ID + Priority + Project --}}
            <div class="flex flex-wrap items-center gap-1.5">

                <span class="font-mono text-[10px] font-semibold tracking-[0.06em] text-slate-400">
                    #{{ $bug->id }}
                </span>

                @if ($bug->priority)
                    <x-priority-badge :priority="$bug->priority" />
                @endif

                @if ($bug->project?->name)
                    <span class="inline-flex items-center rounded-full border border-slate-200/80 bg-slate-50/80 px-2 py-0.5 text-[10px] font-medium text-slate-400">
                        {{ $bug->project->name }}
                    </span>
                @endif

            </div>

            {{-- Row 2: Title --}}
            <h4
                class="mt-2 truncate text-sm font-semibold leading-snug text-slate-900"
                title="{{ $bug->title }}"
            >
                {{ $bug->title }}
            </h4>

            {{-- Row 3: Reporter + Relative time --}}
            <div class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
                <span class="inline-flex items-center gap-1 text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                         class="h-3.5 w-3.5 text-slate-500" aria-hidden="true">
                        <path d="M10 10a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm-5.5 6.25A4.75 4.75 0 0 1 9.25 11.5h1.5a4.75 4.75 0 0 1 4.75 4.75.75.75 0 0 1-.75.75h-9.5a.75.75 0 0 1-.75-.75Z" />
                    </svg>
                    <span class="font-medium text-slate-600">
                        {{ $bug->guest_name }}
                    </span>
                </span>

                <span class="text-slate-300" aria-hidden="true">·</span>

                <span class="inline-flex items-center gap-1 text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                         class="h-3.5 w-3.5 text-slate-500" aria-hidden="true">
                        <path fill-rule="evenodd"
                              d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.5a.75.75 0 0 0-1.5 0V10c0 .199.079.39.22.53l2.25 2.25a.75.75 0 1 0 1.06-1.06l-2.03-2.03V6.5Z"
                              clip-rule="evenodd" />
                    </svg>
                    <span class="text-slate-500">{{ $reportedAt }}</span>
                </span>
            </div>

        </div>

        {{-- ══ Actions ══ --}}
        <div class="flex shrink-0 items-center gap-1.5">

            @if ($hasPriority)

                {{-- ── Priority ready: Assign is primary, Detail is secondary ── --}}
                <form
                    method="POST"
                    action="{{ route('pm.issues.assign', $bug) }}"
                    class="flex items-center gap-1.5"
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
                        if (!confirm('Tugaskan bug ini ke ' + (assigneeName || 'programmer terpilih') + '?')) {
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
                            class="inline-flex h-8 w-40 items-center justify-between gap-1.5 rounded-lg border bg-white px-2.5 text-xs transition-all duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.25)] focus-visible:ring-offset-1"
                            :class="openAssignee
                                ? 'border-[rgba(138,11,78,0.35)] ring-2 ring-[rgba(138,11,78,0.10)]'
                                : 'border-slate-200 hover:border-[rgba(138,11,78,0.20)] hover:bg-[rgba(138,11,78,0.02)]'"
                        >
                            <span
                                class="truncate"
                                :class="assigneeName ? 'font-medium text-slate-800' : 'text-slate-400'"
                                x-text="assigneeName || 'Pilih programmer'"
                            ></span>

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                class="h-3.5 w-3.5 shrink-0 text-slate-300 transition-transform duration-150"
                                :class="openAssignee ? 'rotate-180 text-[#8a0b4e]' : ''"
                                aria-hidden="true"
                            >
                                <path fill-rule="evenodd"
                                    d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0l-4.25-4.51a.75.75 0 0 1 .02-1.06Z"
                                    clip-rule="evenodd" />
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
                                            : 'text-slate-700 hover:bg-[rgba(138,11,78,0.04)] hover:text-slate-900'"
                                    >
                                        <span
                                            class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[9px] font-bold uppercase"
                                            :class="assigneeId === @js((string) $dev->id)
                                                ? 'bg-[rgba(138,11,78,0.10)] text-[#8a0b4e]'
                                                : 'bg-slate-100 text-slate-500'"
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

                    {{-- tombol submit tetap --}}
                </form>

                {{-- Secondary: Detail --}}
                <a
                    href="{{ route('pm.issues.show', $bug) }}?return={{ urlencode(url()->current()) }}"
                    class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 bg-white px-3.5 text-xs font-semibold text-slate-600 transition-all duration-150 hover:border-[rgba(138,11,78,0.18)] hover:bg-[rgba(138,11,78,0.01)] hover:text-[#8a0b4e] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.25)] focus-visible:ring-offset-1"
                >
                    Detail
                </a>

            @else

                {{-- ── No priority: single primary CTA ── --}}
                <a
                    href="{{ route('pm.issues.show', $bug) }}?return={{ urlencode(url()->current()) }}"
                    class="inline-flex h-8 items-center justify-center rounded-lg bg-[#8a0b4e] px-3.5 text-xs font-semibold text-white transition-all duration-150 hover:bg-[#6d0940] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.30)] focus-visible:ring-offset-1"
                >
                    Set Prioritas
                </a>

            @endif

        </div>

    </div>
</div>