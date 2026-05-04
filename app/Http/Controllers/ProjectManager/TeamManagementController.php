<?php

namespace App\Http\Controllers\ProjectManager;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectManager\StoreUserRequest;
use App\Http\Requests\ProjectManager\UpdateUserRequest;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * TeamManagementController
 *
 * Single page (project-manager/management?tab=team) for managing Users + Projects using modals.
 * This is intentionally kept simple for thesis scope.
 */
class TeamManagementController extends Controller
{
    private function resolveInternalRole(string $roleName): Role
    {
        return Role::query()
            ->select(['id', 'name', 'guard_name'])
            ->where('guard_name', 'web')
            ->where('name', $roleName)
            ->firstOrFail();
    }

    public function index(): View
    {
        // Only internal roles (based on your thesis roles)
        $roles = Role::query()
            ->whereIn('name', ['Project Manager', 'Programmer', 'QA'])
            ->orderBy('name')
            ->get();

        $users = User::query()
            ->with('roles')
            ->orderBy('name')
            ->get();

        $projects = Project::query()
            ->orderBy('name')
            ->get();

        return view('panel.project-manager.team.index', compact('roles', 'users', 'projects'));
    }

    public function storeUser(StoreUserRequest $request): RedirectResponse
    {
        $selectedRole = $this->resolveInternalRole((string) $request->string('role'));

        $user = User::create([
            'name' => (string) $request->string('name'),
            'email' => (string) $request->string('email'),
            'password' => Hash::make((string) $request->string('password')),
            'is_active' => (bool) $request->boolean('is_active', true),
            'email_verified_at' => null,
        ]);

        // Pass resolved Role model so Spatie doesn't perform duplicate role lookups.
        $user->syncRoles([$selectedRole]);

        if ($user->is_active) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()
            ->route('pm.management', ['tab' => 'team'])
            ->with('status', 'User berhasil dibuat.');
    }

    public function updateUser(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $selectedRole = $this->resolveInternalRole((string) $request->string('role'));

        $newEmail = (string) $request->string('email');
        $emailChanged = $newEmail !== $user->email;

        $user->name = (string) $request->string('name');
        $user->email = $newEmail;
        $user->is_active = (bool) $request->boolean('is_active');

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make((string) $request->string('password'));
        }

        $user->save();

        // Pass resolved Role model so Spatie doesn't perform duplicate role lookups.
        $user->syncRoles([$selectedRole]);

        if ($emailChanged && $user->is_active) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()
            ->route('pm.management', ['tab' => 'team'])
            ->with('status', 'User berhasil diupdate.');
    }

    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('Project Manager'), 403);

        // Prevent accidentally deleting yourself (common admin safety guard)
        if ($request->user()?->id === $user->id) {
            return redirect()
                ->route('pm.management', ['tab' => 'team'])
                ->with('status', 'Tidak bisa menghapus akun sendiri.');
        }

        // Ensure role pivot cleanup even if FK/cascade differs between environments.
        DB::table('role_user')
            ->where('user_id', $user->id)
            ->delete();

        $user->delete();

        return redirect()
            ->route('pm.management', ['tab' => 'team'])
            ->with('status', 'User berhasil dihapus.');
    }

    public function storeProject(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('Project Manager'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'platform' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        Project::create($data);

        return redirect()
            ->route('pm.management', ['tab' => 'team'])
            ->with('status', 'Project berhasil dibuat.');
    }

    public function updateProject(Request $request, Project $project): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('Project Manager'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'platform' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        $project->update($data);

        return redirect()
            ->route('pm.management', ['tab' => 'team'])
            ->with('status', 'Project berhasil diupdate.');
    }

    public function destroyProject(Request $request, Project $project): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('Project Manager'), 403);

        $project->delete();

        return redirect()
            ->route('pm.management', ['tab' => 'team'])
            ->with('status', 'Project berhasil dihapus.');
    }
}
