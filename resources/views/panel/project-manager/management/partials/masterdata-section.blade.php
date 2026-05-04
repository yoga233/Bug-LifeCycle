<div
    x-show="tab === 'master'"
    x-cloak
    x-transition.opacity.duration.200ms
>
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-5">
            <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">
                Master Data
            </p>
            <p class="mt-1 text-sm font-medium text-slate-900">
                Kelola data referensi
            </p>
            <p class="mt-1 text-sm text-slate-500">
                Kelola daftar master yang dipakai di form bug report dan workflow internal.
            </p>
        </div>

        <div class="px-6 py-5">
            @include('panel.project-manager.master-data.partials.content')
        </div>
    </div>
</div>