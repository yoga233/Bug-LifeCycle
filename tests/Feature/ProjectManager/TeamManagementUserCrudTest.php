<?php

namespace Tests\Feature\ProjectManager;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamManagementUserCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_pm_can_store_team_user_with_selected_role(): void
    {
        Role::create(['name' => 'Project Manager', 'guard_name' => 'web']);
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);
        Role::create(['name' => 'QA', 'guard_name' => 'web']);

        $pm = User::factory()->create(['is_active' => true]);
        $pm->assignRole('Project Manager');

        $response = $this->actingAs($pm)->post(route('pm.team.users.store'), [
            'name' => 'Optimized Team User',
            'email' => 'optimized-team-user@example.test',
            'password' => 'Password1234!@#',
            'password_confirmation' => 'Password1234!@#',
            'role' => 'Programmer',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('pm.management', ['tab' => 'team']));

        $user = User::where('email', 'optimized-team-user@example.test')->firstOrFail();

        $this->assertTrue($user->hasRole('Programmer'));
        $this->assertFalse($user->hasRole('QA'));
    }

    public function test_pm_can_update_team_user_and_switch_role(): void
    {
        Role::create(['name' => 'Project Manager', 'guard_name' => 'web']);
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);
        Role::create(['name' => 'QA', 'guard_name' => 'web']);

        $pm = User::factory()->create(['is_active' => true]);
        $pm->assignRole('Project Manager');

        $member = User::factory()->create([
            'name' => 'Existing Member',
            'email' => 'existing-member@example.test',
            'is_active' => true,
        ]);
        $member->assignRole('Programmer');

        $response = $this->actingAs($pm)->put(route('pm.team.users.update', $member), [
            'name' => 'Existing Member Updated',
            'email' => 'existing-member@example.test',
            'role' => 'QA',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('pm.management', ['tab' => 'team']));

        $member->refresh();

        $this->assertSame('Existing Member Updated', $member->name);
        $this->assertTrue($member->hasRole('QA'));
        $this->assertFalse($member->hasRole('Programmer'));
    }
}
