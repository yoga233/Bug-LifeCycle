<?php

namespace Tests\Feature\ProjectManager;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_pm_can_access_user_management_pages(): void
    {
        Role::create(['name' => 'Project Manager', 'guard_name' => 'web']);
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);

        $pm = User::factory()->create(['is_active' => true]);
        $pm->syncRoles(['Project Manager']);

        $dev = User::factory()->create(['is_active' => true]);
        $dev->syncRoles(['Programmer']);

        // Users are managed via /project-manager/management (hub)
        $this->actingAs($dev)->get('/project-manager/management')->assertStatus(403);
        $this->actingAs($pm)->get('/project-manager/management')->assertStatus(200);
    }
}
