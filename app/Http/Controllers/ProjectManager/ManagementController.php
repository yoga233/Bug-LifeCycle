<?php

namespace App\Http\Controllers\ProjectManager;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\View\View;

class ManagementController extends Controller
{
    public function index(): View
    {
        // Team data
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
            ->paginate(10, ['*'], 'projects_page')
            ->withQueryString()
            ->appends(['tab' => 'projects']);

        // Master Data - use cached data for better performance
        $severities = app('cached_severities');
        $priorities = app('cached_priorities');

        return view('panel.project-manager.management.index', compact(
            'roles',
            'users',
            'projects',
            'severities',
            'priorities',
        ));
    }
}
