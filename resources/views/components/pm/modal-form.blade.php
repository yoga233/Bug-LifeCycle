@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl',
])

<x-modal :name="$name" :show="$show" :maxWidth="$maxWidth">
    <div {{ $attributes->merge(['class' => 'p-6 sm:p-7']) }}>
        {{-- Header --}}
        <div class="min-w-0">
            @isset($title)
                <h2 class="text-lg font-semibold tracking-tight text-slate-900">{{ $title }}</h2>
            @endisset

            @isset($description)
                <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $description }}</p>
            @endisset
        </div>

        <div class="mt-5">
            {{ $slot }}
        </div>
    </div>
</x-modal>
