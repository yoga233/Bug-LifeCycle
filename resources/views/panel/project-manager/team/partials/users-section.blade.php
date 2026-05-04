{{-- USERS VIEW (default) --}}
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">
            Tim
        </p>
        <p class="mt-1 text-sm font-medium text-slate-900">Users</p>
        <p class="mt-1 text-xs text-slate-500">
            Kelola akun dan peran anggota tim.
        </p>
    </div>

    <button
        type="button"
        class="inline-flex h-9 items-center justify-center gap-2 rounded-xl px-4 text-xs font-medium text-white transition-colors"
        style="background-color: #8a0b4e;"
        onmouseover="this.style.backgroundColor='#6d0940'"
        onmouseout="this.style.backgroundColor='#334155'"
        x-data
        x-on:click.prevent="$dispatch('open-modal', 'create-user')"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2"
             class="h-3.5 w-3.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Add User
    </button>
</div>

<div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
    @foreach($users as $u)
        @php
            $roleName = $u->getRoleNames()->first() ?? '-';
            $avatar   = strtoupper(substr($u->name, 0, 1));
        @endphp

        <div class="group overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition-all duration-200 hover:border-slate-300 hover:shadow-[0_8px_24px_-16px_rgba(15,23,42,0.18)]">
            <div class="p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-sm font-semibold text-white"
                            style="background-color: #8a0b4e;"
                        >
                            {{ $avatar }}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900">
                                {{ $u->name }}
                            </p>
                            <span class="mt-1 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium {{ $roleBadge($roleName) }}">
                                {{ $roleName }}
                            </span>
                        </div>
                    </div>

                    <div class="flex shrink-0 gap-1">
                        <button
                            type="button"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition-colors hover:border-slate-700/10 hover:bg-slate-100 hover:text-slate-700"
                            x-data
                            x-on:click.prevent="$dispatch('open-modal', 'edit-user-{{ $u->id }}')"
                            title="Edit"
                        >
                            <x-icon name="pencil-line" class="h-4 w-4" />
                        </button>

                        <button
                            type="button"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition-colors hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600"
                            x-data
                            x-on:click.prevent="$dispatch('open-modal', 'delete-user-{{ $u->id }}')"
                            title="Delete user"
                        >
                            <x-icon name="user-x" class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <div class="mt-4 space-y-2 border-t border-slate-100 pt-4">
                    <p class="truncate text-xs text-slate-500">
                        {{ $u->email }}
                    </p>
                    <div class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full {{ $u->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        <span class="text-xs text-slate-500">
                            {{ $u->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
