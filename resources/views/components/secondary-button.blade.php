<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'inline-flex items-center justify-center rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition-all duration-200 hover:border-[#8a0b4e]/15 hover:bg-[#8a0b4e]/[0.02] hover:text-[#8a0b4e] focus:outline-none'
]) }}>
    {{ $slot }}
</button>