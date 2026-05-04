<div class="flex justify-center">
    <div class="inline-flex items-center rounded-2xl border border-slate-200/80 bg-white p-1 shadow-sm">
        <button
            type="button"
            class="rounded-xl px-5 py-2.5 text-sm font-medium transition-all duration-150"
            :class="tab === 'users'
                ? 'text-white shadow-sm'
                : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700'"
            :style="tab === 'users' ? 'background-color:#8a0b4e' : ''"
            @click="setTab('users')"
        >
            Users
        </button>

        <button
            type="button"
            class="rounded-xl px-5 py-2.5 text-sm font-medium transition-all duration-150"
            :class="tab === 'projects'
                ? 'text-white shadow-sm'
                : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700'"
            :style="tab === 'projects' ? 'background-color:#8a0b4e' : ''"
            @click="setTab('projects')"
        >
            Projects
        </button>
    </div>
</div>