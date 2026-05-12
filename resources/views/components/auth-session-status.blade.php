@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'flex items-center gap-3 rounded-xl bg-emerald-50/50 border border-emerald-100/80 p-3.5 shadow-sm shadow-emerald-500/5 animate-in zoom-in-95 duration-500']) }}>
        <div class="flex-shrink-0 flex items-center justify-center rounded-lg bg-emerald-500/10 p-1.5 ring-1 ring-emerald-500/20">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 6 9 17l-5-5"/>
            </svg>
        </div>
        <div class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider leading-none">
            {{ $status }}
        </div>
    </div>
@endif
