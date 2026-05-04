@props([
    /**
     * Variant color rules (PM style):
     * - primary  : aksi utama / lanjutkan proses
     * - secondary: aksi pendamping (reset, kembali, detail)
     * - danger   : aksi berisiko / pembatalan / rollback
     */
    'variant' => 'primary',
    'size' => 'md',
    'block' => false,
    'type' => 'button',
    /**
     * color can override default variant mapping when needed.
     * Supported: blue, green, amber, purple, red, slate
     */
    'color' => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 rounded-md font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed';

    $sizeClasses = match ((string) $size) {
        'sm' => 'h-9 px-3 text-xs',
        default => 'h-10 px-4 text-sm',
    };

    $resolveSolidColorClasses = function (string $name): string {
        return match ($name) {
            'green' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500',
            'amber' => 'bg-amber-600 text-white hover:bg-amber-700 focus:ring-amber-500',
            'purple' => 'bg-purple-600 text-white hover:bg-purple-700 focus:ring-purple-500',
            'red' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
            'slate' => 'bg-slate-600 text-white hover:bg-slate-700 focus:ring-slate-500',
            default => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
        };
    };

    $resolveSoftColorClasses = function (string $name): string {
        return match ($name) {
            'green' => 'border border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 focus:ring-emerald-500',
            'amber' => 'border border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100 focus:ring-amber-500',
            'purple' => 'border border-purple-300 bg-purple-50 text-purple-800 hover:bg-purple-100 focus:ring-purple-500',
            'red' => 'border border-red-300 bg-red-50 text-red-800 hover:bg-red-100 focus:ring-red-500',
            'slate' => 'border border-slate-300 bg-slate-50 text-slate-800 hover:bg-slate-100 focus:ring-slate-500',
            default => 'border border-blue-300 bg-blue-50 text-blue-800 hover:bg-blue-100 focus:ring-blue-500',
        };
    };

    $normalizedColor = is_string($color) ? strtolower(trim($color)) : null;

    $variantClasses = match ((string) $variant) {
        'secondary' => $resolveSoftColorClasses($normalizedColor ?: 'slate'),
        'danger' => $resolveSolidColorClasses('red'),
        default => $resolveSolidColorClasses($normalizedColor ?: 'blue'),
    };

    $widthClasses = $block ? 'w-full' : '';
@endphp

<button {{ $attributes->merge(['type' => $type, 'class' => trim("{$baseClasses} {$sizeClasses} {$variantClasses} {$widthClasses}")]) }}>
    {{ $slot }}
</button>