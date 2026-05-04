@props([
    'status' => null,
    'variant' => 'outline',
    'dot' => false,
])

@php
    $st = trim((string) $status);

    /**
     * UI labels
     * - Rejected = QA menolak hasil fix dan mengembalikan ke programmer
     */
    $labelMap = [
        'Reported'    => 'Dilaporkan',
        'Assigned'    => 'Ditugaskan',
        'In Progress' => 'Dalam Pengerjaan',
        'Testing'     => 'Pengujian',
        'Resolved'    => 'Diselesaikan',
        'Closed'      => 'Ditutup',
        'Rejected'    => 'Dikembalikan QA',
    ];

    $label = $labelMap[$st] ?? ($st !== '' ? $st : '-');

    /**
     * SaaS/global semantic colors
     * - Reported    => amber   (new / needs attention)
     * - Assigned    => sky     (already assigned)
     * - In Progress => blue    (active work)
     * - Testing     => violet  (verification phase)
     * - Resolved    => emerald (fixed)
     * - Closed      => slate   (final / inactive)
     * - Rejected    => rose    (returned by QA / needs rework)
     */
    $colorMap = [
        'Reported'    => 'amber',
        'Assigned'    => 'sky',
        'In Progress' => 'blue',
        'Testing'     => 'violet',
        'Resolved'    => 'emerald',
        'Closed'      => 'slate',
        'Rejected'    => 'rose',
    ];

    $color = $colorMap[$st] ?? 'slate';

    $dotClassMap = [
        'amber'   => 'bg-amber-500',
        'sky'     => 'bg-sky-500',
        'blue'    => 'bg-blue-500',
        'violet'  => 'bg-violet-500',
        'emerald' => 'bg-emerald-500',
        'slate'   => 'bg-slate-500',
        'rose'    => 'bg-rose-500',
    ];

    $dotClass = $dotClassMap[$color] ?? 'bg-slate-400';

    $base = 'inline-flex items-center shrink-0 rounded-full whitespace-nowrap leading-none';

    $size = match ((string) $variant) {
        'pill' => 'px-3 py-1 text-[11px] font-semibold',
        'soft', 'outline' => 'px-2.5 py-0.5 text-[10px] font-semibold',
        'text' => 'text-xs font-medium',
        default => 'px-2.5 py-0.5 text-[10px] font-semibold',
    };

    $gap = $dot ? 'gap-1.5' : 'gap-0';

    $colorClasses = match ((string) $variant) {
        'text' => match ($color) {
            'amber'   => 'text-amber-700',
            'sky'     => 'text-sky-700',
            'blue'    => 'text-blue-700',
            'violet'  => 'text-violet-700',
            'emerald' => 'text-emerald-700',
            'slate'   => 'text-slate-700',
            'rose'    => 'text-rose-700',
            default   => 'text-slate-700',
        },

        'pill' => match ($color) {
            'amber'   => 'bg-amber-50 text-amber-700',
            'sky'     => 'bg-sky-50 text-sky-700',
            'blue'    => 'bg-blue-50 text-blue-700',
            'violet'  => 'bg-violet-50 text-violet-700',
            'emerald' => 'bg-emerald-50 text-emerald-700',
            'slate'   => 'bg-slate-100 text-slate-700',
            'rose'    => 'bg-rose-50 text-rose-700',
            default   => 'bg-slate-100 text-slate-700',
        },

        'soft' => match ($color) {
            'amber'   => 'bg-amber-100 text-amber-700',
            'sky'     => 'bg-sky-100 text-sky-700',
            'blue'    => 'bg-blue-100 text-blue-700',
            'violet'  => 'bg-violet-100 text-violet-700',
            'emerald' => 'bg-emerald-100 text-emerald-700',
            'slate'   => 'bg-slate-100 text-slate-700',
            'rose'    => 'bg-rose-100 text-rose-700',
            default   => 'bg-slate-100 text-slate-700',
        },

        default => match ($color) {
            'amber'   => 'border border-amber-200 bg-amber-50/40 text-amber-700',
            'sky'     => 'border border-sky-200 bg-sky-50/40 text-sky-700',
            'blue'    => 'border border-blue-200 bg-blue-50/40 text-blue-700',
            'violet'  => 'border border-violet-200 bg-violet-50/40 text-violet-700',
            'emerald' => 'border border-emerald-200 bg-emerald-50/40 text-emerald-700',
            'slate'   => 'border border-slate-200 bg-slate-50/40 text-slate-700',
            'rose'    => 'border border-rose-200 bg-rose-50/40 text-rose-700',
            default   => 'border border-slate-200 bg-slate-50/40 text-slate-700',
        },
    };

    $slotText = trim((string) $slot);
    $display = $slotText !== '' ? $slot : $label;

    $containerClass = trim("$base $size $gap $colorClasses");
@endphp

<span {{ $attributes->merge(['class' => $containerClass]) }}>
    @if ($dot)
        <span class="h-1 w-1 rounded-full {{ $dotClass }}"></span>
    @endif

    <span>{{ $display }}</span>
</span>