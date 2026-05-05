{{-- Create User Modal --}}
<x-pm.modal-form name="create-user" :show="($errors->any() && old('_modal') === 'create-user')" maxWidth="2xl">
    <x-slot:title>Add User</x-slot:title>
    <x-slot:description>Buat akun internal baru.</x-slot:description>

    <form method="POST" action="{{ route('pm.team.users.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="_modal" value="create-user" />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-input-label for="create_user_name" value="Name" />
                <x-text-input id="create_user_name" name="name" type="text"
                    class="mt-1 block w-full focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]"
                    :value="old('name')" required />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                <p class="mt-1 text-xs text-slate-500">Nama lengkap internal (contoh: "Rina Putri").</p>
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="create_user_email" value="Email" />
                <x-text-input id="create_user_email" name="email" type="email"
                    class="mt-1 block w-full focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]"
                    :value="old('email')" required />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                <p class="mt-1 text-xs text-slate-500">Dipakai untuk login &amp; notifikasi.</p>
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="create_user_role" value="Role" />
                <select id="create_user_role" name="role"
                    class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]"
                    required>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" @selected(old('role') === $role->name)>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('role')" />
                <p class="mt-1 text-xs text-slate-500">Tentukan akses: PM, Programmer, atau QA.</p>
            </div>

            <div>
                <x-input-label for="create_user_password" value="Password" />
                <x-text-input id="create_user_password" name="password" type="password"
                    class="mt-1 block w-full focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]"
                    required />
                <x-input-error class="mt-2" :messages="$errors->get('password')" />
                <p class="mt-1 text-xs text-slate-500">Minimal 8 karakter.</p>
            </div>

            <div>
                <x-input-label for="create_user_password_confirmation" value="Confirm Password" />
                <x-text-input id="create_user_password_confirmation" name="password_confirmation" type="password"
                    class="mt-1 block w-full focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]"
                    required />
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
            <div class="flex items-start gap-3">
                <input id="create_user_is_active" name="is_active" type="checkbox" value="1"
                    class="mt-1 rounded border-slate-300 text-[#8a0b4e] focus:ring-[#f5e8ef]"
                    checked>
                <div class="min-w-0">
                    <label for="create_user_is_active" class="text-sm font-medium text-slate-900">
                        Active account
                    </label>
                    <p class="mt-0.5 text-xs text-slate-500">
                        Jika nonaktif, user tidak bisa login tetapi data tetap tersimpan.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-secondary-button
                class="justify-center transition-colors hover:border-slate-700/20 hover:text-slate-700"
                x-on:click="$dispatch('close-modal', 'create-user')"
            >Cancel</x-secondary-button>

            <x-primary-button
                class="justify-center normal-case tracking-normal"
                style="background-color:#8a0b4e;"
                onmouseover="this.style.backgroundColor='#6d0940'"
                onmouseout="this.style.backgroundColor='#8a0b4e'"
            >Create User</x-primary-button>
        </div>
    </form>
</x-pm.modal-form>

{{-- Edit + Delete modals per user --}}
@foreach($users as $u)
    @php $roleName = $u->getRoleNames()->first(); @endphp

    <x-pm.modal-form name="edit-user-{{ $u->id }}" :show="($errors->any() && old('_modal') === 'edit-user-{{ $u->id }}')" maxWidth="2xl">
        <x-slot:title>Edit User</x-slot:title>
        <x-slot:description>Update data user.</x-slot:description>

        <form method="POST" action="{{ route('pm.team.users.update', $u) }}" class="space-y-5">
            @csrf
            @method('PUT')
            <input type="hidden" name="_modal" value="edit-user-{{ $u->id }}" />

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-input-label for="edit_user_name_{{ $u->id }}" value="Name" />
                    <x-text-input id="edit_user_name_{{ $u->id }}" name="name" type="text"
                        class="mt-1 block w-full focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]"
                        :value="old('name', $u->name)" required />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="edit_user_email_{{ $u->id }}" value="Email" />
                    <x-text-input id="edit_user_email_{{ $u->id }}" name="email" type="email"
                        class="mt-1 block w-full focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]"
                        :value="old('email', $u->email)" required />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="edit_user_role_{{ $u->id }}" value="Role" />
                    <select id="edit_user_role_{{ $u->id }}" name="role"
                        class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]"
                        required>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" @selected(old('role', $roleName) === $role->name)>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                <div class="flex items-start gap-3">
                    <input type="hidden" name="is_active" value="0" />
                    <input id="edit_user_is_active_{{ $u->id }}" name="is_active" type="checkbox" value="1"
                        class="mt-1 rounded border-slate-300 text-[#8a0b4e] focus:ring-[#f5e8ef]"
                        @checked(old('is_active', $u->is_active))>
                    <div>
                        <label for="edit_user_is_active_{{ $u->id }}" class="text-sm font-medium text-slate-900">
                            Active account
                        </label>
                        <p class="mt-0.5 text-xs text-slate-500">
                            Matikan untuk mencegah login sementara.
                        </p>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-5">
                <p class="text-sm font-medium text-slate-900">Change password</p>
                <p class="mt-0.5 text-xs text-slate-500">
                    Optional. Biarkan kosong jika tidak ingin mengganti.
                </p>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="edit_user_password_{{ $u->id }}" value="New Password" />
                        <x-text-input id="edit_user_password_{{ $u->id }}" name="password" type="password"
                            class="mt-1 block w-full focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]" />
                    </div>

                    <div>
                        <x-input-label for="edit_user_password_confirmation_{{ $u->id }}" value="Confirm New Password" />
                        <x-text-input id="edit_user_password_confirmation_{{ $u->id }}" name="password_confirmation" type="password"
                            class="mt-1 block w-full focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]" />
                    </div>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-secondary-button
                    class="justify-center transition-colors hover:border-slate-700/20 hover:text-slate-700"
                    x-on:click="$dispatch('close-modal', 'edit-user-{{ $u->id }}')"
                >Cancel</x-secondary-button>

                <x-primary-button
                    class="justify-center normal-case tracking-normal"
                    style="background-color:#8a0b4e;"
                    onmouseover="this.style.backgroundColor='#6d0940'"
                    onmouseout="this.style.backgroundColor='#8a0b4e'"
                >Save changes</x-primary-button>
            </div>
        </form>
    </x-pm.modal-form>

    <x-pm.modal-confirm
    name="delete-user-{{ $u->id }}"
    :show="($errors->any() && old('_modal') === 'delete-user-{{ $u->id }}')"
    maxWidth="lg"
    variant="danger"
>
    <x-slot:title>Delete User</x-slot:title>
    <x-slot:description>
        Hapus user <span class="font-medium text-slate-700">{{ $u->name }}</span>?
        Aksi ini tidak dapat dibatalkan.
    </x-slot:description>

    <x-slot:icon>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-rose-100 bg-rose-50">
            <x-icon name="user-x" class="h-5 w-5 text-rose-500" />
        </div>
    </x-slot:icon>

    <form method="POST" action="{{ route('pm.team.users.destroy', $u) }}" class="mt-6">
        @csrf
        @method('DELETE')
        <input type="hidden" name="_modal" value="delete-user-{{ $u->id }}" />

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-secondary-button
                class="justify-center transition-colors hover:border-slate-700/20 hover:text-slate-700"
                x-on:click="$dispatch('close-modal', 'delete-user-{{ $u->id }}')"
            >Cancel</x-secondary-button>

            <button
                type="submit"
                class="inline-flex h-9 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-5 text-xs font-medium text-rose-600 transition-colors duration-200 hover:border-rose-300 hover:bg-rose-100 hover:text-rose-700"
            >
                Delete User
            </button>
        </div>
    </form>
</x-pm.modal-confirm>
@endforeach

{{-- Create Project Modal --}}
<x-pm.modal-form name="create-project" :show="($errors->any() && old('_modal') === 'create-project')" maxWidth="2xl">
    <x-slot:title>Add Project</x-slot:title>
    <x-slot:description>Tambahkan project baru.</x-slot:description>

    <form method="POST" action="{{ route('pm.team.projects.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="_modal" value="create-project" />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-input-label for="create_project_name" value="Project name" />
                <x-text-input
                    id="create_project_name"
                    name="name"
                    type="text"
                    class="mt-1 block w-full focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]"
                    required
                />
                <p class="mt-1 text-xs text-slate-500">
                    Nama yang akan tampil di seluruh modul bug.
                </p>
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="create_project_platform" value="Platform" />
                <x-text-input
                    id="create_project_platform"
                    name="platform"
                    type="text"
                    class="mt-1 block w-full focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]"
                    required
                />
                <p class="mt-1 text-xs text-slate-500">Contoh: Web, Android, iOS.</p>
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="create_project_description" value="Description (optional)" />
                <textarea
                    id="create_project_description"
                    name="description"
                    rows="4"
                    placeholder="Ringkasan singkat project..."
                    class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 placeholder:text-slate-300 transition-colors duration-200 focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]"
                ></textarea>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-secondary-button
                class="justify-center transition-colors hover:border-slate-700/20 hover:text-slate-700"
                x-on:click="$dispatch('close-modal', 'create-project')"
            >
                Cancel
            </x-secondary-button>

            <x-primary-button
                class="justify-center normal-case tracking-normal transition-colors duration-200"
                style="background-color:#8a0b4e;"
                onmouseover="this.style.backgroundColor='#6d0940'"
                onmouseout="this.style.backgroundColor='#8a0b4e'"
            >
                Create Project
            </x-primary-button>
        </div>
    </form>
</x-pm.modal-form>

{{-- Edit + Delete Project modals --}}
@foreach($projects as $p)
    <x-pm.modal-form name="edit-project-{{ $p->id }}" :show="($errors->any() && old('_modal') === 'edit-project-{{ $p->id }}')" maxWidth="2xl">
        <x-slot:title>Edit Project</x-slot:title>

        <form method="POST" action="{{ route('pm.team.projects.update', $p) }}" class="space-y-5">
            @csrf
            @method('PUT')
            <input type="hidden" name="_modal" value="edit-project-{{ $p->id }}" />

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-input-label for="edit_project_name_{{ $p->id }}" value="Project name" />
                    <x-text-input id="edit_project_name_{{ $p->id }}" name="name" type="text"
                        class="mt-1 block w-full focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]"
                        :value="$p->name" required />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="edit_project_platform_{{ $p->id }}" value="Platform" />
                    <x-text-input id="edit_project_platform_{{ $p->id }}" name="platform" type="text"
                        class="mt-1 block w-full focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]"
                        :value="$p->platform" required />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="edit_project_description_{{ $p->id }}" value="Description (optional)" />
                    <textarea
                        id="edit_project_description_{{ $p->id }}"
                        name="description"
                        rows="4"
                        class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 placeholder:text-slate-300 focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]"
                    >{{ $p->description }}</textarea>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-secondary-button
                    class="justify-center transition-colors hover:border-slate-700/20 hover:text-slate-700"
                    x-on:click="$dispatch('close-modal', 'edit-project-{{ $p->id }}')"
                >Cancel</x-secondary-button>

                <x-primary-button
                    class="justify-center normal-case tracking-normal"
                    style="background-color:#8a0b4e;"
                    onmouseover="this.style.backgroundColor='#6d0940'"
                    onmouseout="this.style.backgroundColor='#8a0b4e'"
                >Save changes</x-primary-button>
            </div>
        </form>
    </x-pm.modal-form>

    <x-pm.modal-confirm
        name="delete-project-{{ $p->id }}"
        :show="($errors->any() && old('_modal') === 'delete-project-{{ $p->id }}')"
        maxWidth="lg"
        variant="danger"
    >
        <x-slot:title>Delete Project</x-slot:title>
        <x-slot:description>
            Hapus project <span class="font-medium">{{ $p->name }}</span>?
            Aksi ini tidak dapat dibatalkan.
        </x-slot:description>

        <form method="POST" action="{{ route('pm.team.projects.destroy', $p) }}" class="mt-6">
            @csrf
            @method('DELETE')
            <input type="hidden" name="_modal" value="delete-project-{{ $p->id }}" />

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-secondary-button
                    class="justify-center transition-colors hover:border-slate-700/20 hover:text-slate-700"
                    x-on:click="$dispatch('close-modal', 'delete-project-{{ $p->id }}')"
                >Cancel</x-secondary-button>

                <x-danger-button class="justify-center">Delete</x-danger-button>
            </div>
        </form>
    </x-pm.modal-confirm>
@endforeach