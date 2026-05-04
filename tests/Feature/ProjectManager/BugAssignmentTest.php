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

class BugAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_pm_can_set_priority_when_bug_status_is_reported(): void
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
            'title' => 'Bug Priority',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Reported',
        ]);

        $this->actingAs($pm)
            ->post(route('pm.issues.priority.update', $bug), ['priority_id' => $priority->id])
            ->assertRedirect();

        $this->assertDatabaseHas('bugs', [
            'id' => $bug->id,
            'priority_id' => $priority->id,
            'status' => 'Reported',
        ]);
    }

    public function test_pm_cannot_set_priority_when_bug_status_is_not_reported(): void
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
            'title' => 'Bug Priority Locked',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Assigned',
        ]);

        $this->actingAs($pm)
            ->post(route('pm.issues.priority.update', $bug), ['priority_id' => $priority->id])
            ->assertRedirect();

        $this->assertDatabaseHas('bugs', [
            'id' => $bug->id,
            'priority_id' => null,
            'status' => 'Assigned',
        ]);
    }

    public function test_pm_cannot_assign_when_reported_bug_has_no_priority(): void
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
            'title' => 'Bug Assign needs priority',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Reported',
        ]);

        $this->actingAs($pm)
            ->post(route('pm.issues.assign', $bug), ['assignee_id' => $dev->id])
            ->assertRedirect();

        $this->assertDatabaseHas('bugs', [
            'id' => $bug->id,
            'assignee_id' => null,
            'status' => 'Reported',
        ]);
    }

    public function test_pm_can_assign_programmer_and_bug_moves_to_assigned_with_history(): void
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
            'title' => 'Bug Assign',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Reported',
        ]);

        $this->actingAs($pm)
            ->post(route('pm.issues.assign', $bug), ['assignee_id' => $dev->id])
            ->assertRedirect();

        $this->assertDatabaseHas('bugs', [
            'id' => $bug->id,
            'assignee_id' => $dev->id,
            'status' => 'Assigned',
        ]);

        $this->assertDatabaseHas('bug_status_histories', [
            'bug_id' => $bug->id,
            'old_status' => 'Reported',
            'new_status' => 'Assigned',
            'user_id' => $pm->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $dev->id,
            'related_id' => $bug->id,
            'type' => 'BugAssigned',
        ]);
    }

    public function test_pm_can_unassign_when_status_assigned_and_bug_back_to_reported_with_history(): void
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
            'title' => 'Bug Unassign',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Assigned',
        ]);

        $this->actingAs($pm)
            ->post(route('pm.issues.unassign', $bug))
            ->assertRedirect();

        $this->assertDatabaseHas('bugs', [
            'id' => $bug->id,
            'assignee_id' => null,
            'status' => 'Reported',
        ]);

        $this->assertDatabaseHas('bug_status_histories', [
            'bug_id' => $bug->id,
            'old_status' => 'Assigned',
            'new_status' => 'Reported',
            'user_id' => $pm->id,
        ]);
    }
}
