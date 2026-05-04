{{-- PROJECTS --}}
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">
            Tim
        </p>
        <p class="mt-1 text-sm font-medium text-slate-900">Projects</p>
        <p class="mt-1 text-xs text-slate-500">
            Kelola project yang aktif dan penugasannya.
        </p>
    </div>

    <button
        type="button"
        class="inline-flex h-9 items-center justify-center gap-2 rounded-xl px-4 text-xs font-medium text-white transition-colors"
        style="background-color: #8a0b4e;"
        onmouseover="this.style.backgroundColor='#6d0940'"
        onmouseout="this.style.backgroundColor='#334155'"
        x-data
        x-on:click.prevent="$dispatch('open-modal', 'create-project')"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2"
             class="h-3.5 w-3.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Add Project
    </button>
</div>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
    @foreach($projects as $p)
        <div class="group overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition-all duration-200 hover:border-slate-300 hover:shadow-[0_8px_24px_-16px_rgba(15,23,42,0.18)]">
            <div class="p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-900">
                            {{ $p->name }}
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium {{ $projectBadge() }}">
                                Active
                            </span>
                            <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-medium text-slate-600">
                                {{ $p->platform }}
                            </span>
                        </div>
                    </div>

                    <div class="flex shrink-0 gap-1">
                        <button
                            type="button"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition-colors hover:border-slate-700/20 hover:bg-slate-100 hover:text-slate-700"
                            x-data
                            x-on:click.prevent="$dispatch('open-modal', 'edit-project-{{ $p->id }}')"
                            title="Edit"
                        >
                            <x-icon name="pencil-line" class="h-4 w-4" />
                        </button>

                        <button
                            type="button"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-500 transition-colors hover:border-rose-300 hover:bg-rose-100 hover:text-rose-700"
                            x-data
                            x-on:click.prevent="$dispatch('open-modal', 'delete-project-{{ $p->id }}')"
                            title="Delete"
                        >
                            <x-icon name="trash-2" class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                @if($p->description)
                    <p class="mt-4 border-t border-slate-100 pt-4 text-xs leading-relaxed text-slate-500">
                        {{ $p->description }}
                    </p>
                @endif
            </div>
        </div>
    @endforeach
</div>

@if (method_exists($projects, 'hasPages') && $projects->hasPages())
    <x-pagination :paginator="$projects" />
@endif
