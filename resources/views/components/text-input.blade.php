@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-200 focus:border-[#8a0b4e] focus:ring-[#8a0b4e] focus:ring-opacity-20 rounded-xl shadow-sm transition-all duration-150']) }}>
