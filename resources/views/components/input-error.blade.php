@props(['messages'])

@if ($messages)
    <div {{ $attributes->merge(['class' => 'flex items-center gap-1.5 mt-1.5 px-0.5 animate-in fade-in slide-in-from-top-1 duration-300']) }}>
        {{-- Minimalist Warning Icon --}}
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-red-500 flex-shrink-0">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        
        <ul class="text-[11px] font-bold text-red-500 tracking-tight leading-none uppercase">
            @foreach ((array) $messages as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
