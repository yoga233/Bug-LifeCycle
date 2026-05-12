<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#8a0b4e] border border-transparent rounded-xl font-bold text-[11px] text-white uppercase tracking-widest hover:bg-[#6d0940] focus:bg-[#6d0940] active:bg-[#3a021f] focus:outline-none focus:ring-2 focus:ring-[#8a0b4e] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
