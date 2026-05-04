<?php

namespace Tests\Feature\ProjectManager;

use App\Models\Bug;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Role;
use App\Models\Severity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BugAssignmentAjaxTest extends TestCase
{
    use RefreshDatabase;

    public function test_pm_can_set_priority_via_ajax_when_bug_is_reported(): void
    {
        Role::create(['name' => 'Project Manager', 'guard_name' => 'web']);

        $pm = User::factory()->create(['is_active' => true]);
        $pm->assignRole('Project Manager');

        $project = Project::create(['name' => 'Demo', 'platform' => 'Web']);
        $severity = Severity::create(['level' => 'Minor', 'description' => 'Minor']);
        $priority = Priority::create(['level' => 'High', 'sla_hours' => 24]);

        $bug = Bug::create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => null,
            'assignee_id' => null,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0',
            'title' => 'Bug Priority Ajax',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Reported',
        ]);

        $this->actingAs($pm)
            ->postJson(route('pm.issues.priority.update', $bug), ['priority_id' => $priority->id])
            ->assertOk()
            ->assertJsonPath('bug.id', $bug->id)
            ->assertJsonPath('bug.status', 'Reported')
            ->assertJsonPath('bug.priority.id', $priority->id)
            ->assertJsonPath('bug.priority.level', $priority->level)
            ->assertJsonPath('bug.priority.sla_hours', $priority->sla_hours);

        $this->assertDatabaseHas('bugs', [
            'id' => $bug->id,
            'priority_id' => $priority->id,
        ]);
    }

    public function test_pm_cannot_set_priority_via_ajax_when_bug_status_is_not_reported(): void
    {
        Role::create(['name' => 'Project Manager', 'guard_name' => 'web']);

        $pm = User::factory()->create(['is_active' => true]);
        $pm->assignRole('Project Manager');

        $project = Project::create(['name' => 'Demo', 'platform' => 'Web']);
        $severity = Severity::create(['level' => 'Minor', 'description' => 'Minor']);
        $priority = Priority::create(['level' => 'High', 'sla_hours' => 24]);

        $bug = Bug::create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => null,
            'assignee_id' => null,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0',
            'title' => 'Bug Priority Ajax Locked',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Assigned',
        ]);

        $this->actingAs($pm)
            ->postJson(route('pm.issues.priority.update', $bug), ['priority_id' => $priority->id])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Prioritas hanya bisa diubah ketika status bug masih "Dilaporkan".');

        $this->assertDatabaseHas('bugs', [
            'id' => $bug->id,
            'priority_id' => null,
            'status' => 'Assigned',
        ]);
    }

    public function test_pm_cannot_assign_via_ajax_when_reported_bug_has_no_priority(): void
    {
        Role::create(['name' => 'Project Manager', 'guard_name' => 'web']);
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);

        $pm = User::factory()->create(['is_active' => true]);
        $pm->assignRole('Project Manager');

        $dev = User::factory()->create(['is_active' => true]);
        $dev->assignRole('Programmer');

        $project = Project::create(['name' => 'Demo', 'platform' => 'Web']);
        $severity = Severity::create(['level' => 'Minor', 'description' => 'Minor']);

        $bug = Bug::create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => null,
            'assignee_id' => null,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0',
            'title' => 'Bug Assign Ajax needs priority',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Reported',
        ]);

        $this->actingAs($pm)
            ->postJson(route('pm.issues.assign', $bug), ['assignee_id' => $dev->id])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Tentukan prioritas terlebih dahulu pada detail bug sebelum menugaskan programmer.');

        $this->assertDatabaseHas('bugs', [
            'id' => $bug->id,
            'assignee_id' => null,
            'status' => 'Reported',
        ]);
    }

    public function test_pm_can_assign_programmer_via_ajax_and_get_json_response(): void
    {
        Role::create(['name' => 'Project Manager', 'guard_name' => 'web']);
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);

        $pm = User::factory()->create(['is_active' => true]);
        $pm->assignRole('Project Manager');

        $dev = User::factory()->create(['is_active' => true]);
        $dev->assignRole('Programmer');

        $project = Project::create(['name' => 'Demo', 'platform' => 'Web']);
        $severity = Severity::create(['level' => 'Minor', 'description' => 'Minor']);
        $priority = Priority::create(['level' => 'Medium', 'sla_hours' => 48]);

        $bug = Bug::create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => null,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0',
            'title' => 'Bug Assign Ajax',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Reported',
        ]);

        $this->actingAs($pm)
            ->postJson(route('pm.issues.assign', $bug), ['assignee_id' => $dev->id])
            ->assertOk()
            ->assertJsonPath('bug.id', $bug->id)
            ->assertJsonPath('bug.status', 'Assigned')
            ->assertJsonPath('bug.assignee.id', $dev->id)
            ->assertJsonPath('bug.assignee.name', $dev->name);

        $this->assertDatabaseHas('bugs', [
            'id' => $bug->id,
            'assignee_id' => $dev->id,
            'status' => 'Assigned',
        ]);
    }

    public function test_pm_can_unassign_via_ajax_and_get_json_response(): void
    {
        Role::create(['name' => 'Project Manager', 'guard_name' => 'web']);
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);

        $pm = User::factory()->create(['is_active' => true]);
        $pm->assignRole('Project Manager');

        $dev = User::factory()->create(['is_active' => true]);
        $dev->assignRole('Programmer');

        $project = Project::create(['name' => 'Demo', 'platform' => 'Web']);
        $severity = Severity::create(['level' => 'Minor', 'description' => 'Minor']);

        $bug = Bug::create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => null,
            'assignee_id' => $dev->id,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0',
            'title' => 'Bug Unassign Ajax',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Assigned',
        ]);

        $this->actingAs($pm)
            ->postJson(route('pm.issues.unassign', $bug))
            ->assertOk()
            ->assertJsonPath('bug.id', $bug->id)
            ->assertJsonPath('bug.status', 'Reported')
            ->assertJsonPath('bug.assignee', null);

        $this->assertDatabaseHas('bugs', [
            'id' => $bug->id,
            'assignee_id' => null,
            'status' => 'Reported',
        ]);
    }
}
