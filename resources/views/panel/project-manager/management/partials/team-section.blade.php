<div
    x-show="tab === 'users'"
    x-cloak
    x-transition.opacity.duration.200ms
    class="space-y-6"
>
    @php
        $roleBadge = fn (string $role) => match($role) {
            'Programmer'      => 'border border-slate-200 bg-slate-100 text-slate-700',
            'Project Manager' => 'border border-slate-200 bg-slate-50 text-slate-700',
            'QA'              => 'border border-emerald-100 bg-emerald-50 text-emerald-700',
            default           => 'border border-slate-200 bg-slate-50 text-slate-600',
        };
    @endphp

    @include('panel.project-manager.team.partials.users-section')
</div>

<div
    x-show="tab === 'projects'"
    x-cloak
    x-transition.opacity.duration.200ms
    class="space-y-6"
>
    @php
        $projectBadge = fn () => 'border border-slate-200 bg-slate-100 text-slate-700';
    @endphp

    @include('panel.project-manager.team.partials.projects-section')
</div>