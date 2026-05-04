<?php

namespace Tests\Feature;

use App\Models\Bug;
use App\Models\Notification;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Role;
use App\Models\Severity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BugLifecycleTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_programmer_can_move_assigned_to_in_progress_and_history_written(): void
    {
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);

        $dev = User::factory()->create(['is_active' => true]);
        $dev->assignRole('Programmer');

        $proj = Project::create(['name' => 'Sistem Gudang', 'platform' => 'Web']);
        $sev = Severity::create(['level' => 'Critical', 'description' => 'Mati total']);
        $prio = Priority::create(['level' => 'High', 'sla_hours' => 24]);

        $bug = Bug::create([
            'project_id' => $proj->id,
            'severity_id' => $sev->id,
            'priority_id' => $prio->id,
            'assignee_id' => $dev->id,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0',
            'title' => 'Bug A',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Assigned',
        ]);

        $this->actingAs($dev)
            ->post(route('programmer.bugs.start', $bug))
            ->assertRedirect();

        $this->assertDatabaseHas('bugs', [
            'id' => $bug->id,
            'status' => 'In Progress',
        ]);

        $this->assertDatabaseHas('bug_status_histories', [
            'bug_id' => $bug->id,
            'old_status' => 'Assigned',
            'new_status' => 'In Progress',
            'user_id' => $dev->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $dev->id,
            'related_id' => $bug->id,
            'type' => 'BugStatusChanged',
        ]);
    }

    public function test_programmer_send_to_testing_creates_notification_for_active_qa(): void
    {
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);
        Role::create(['name' => 'QA', 'guard_name' => 'web']);

        $dev = User::factory()->create(['is_active' => true]);
        $dev->assignRole('Programmer');

        $qa = User::factory()->create(['is_active' => true]);
        $qa->assignRole('QA');

        $inactiveQa = User::factory()->create(['is_active' => false]);
        $inactiveQa->assignRole('QA');

        $proj = Project::create(['name' => 'Sistem Gudang', 'platform' => 'Web']);
        $sev = Severity::create(['level' => 'Minor', 'description' => 'Minor']);

        $bug = Bug::create([
            'project_id' => $proj->id,
            'severity_id' => $sev->id,
            'priority_id' => null,
            'assignee_id' => $dev->id,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0',
            'title' => 'Bug Testing Notification',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'In Progress',
        ]);

        $this->actingAs($dev)
            ->post(route('programmer.bugs.sendToTesting', $bug))
            ->assertRedirect();

        $this->assertDatabaseHas('bugs', [
            'id' => $bug->id,
            'status' => 'Testing',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $qa->id,
            'related_id' => $bug->id,
            'type' => 'BugStatusChanged',
            'is_read' => false,
        ]);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $inactiveQa->id,
            'related_id' => $bug->id,
            'type' => 'BugStatusChanged',
        ]);
    }

    public function test_qa_can_approve_testing_to_resolved_and_history_written(): void
    {
        Role::create(['name' => 'QA', 'guard_name' => 'web']);
        Role::create(['name' => 'Project Manager', 'guard_name' => 'web']);

        $qa = User::factory()->create(['is_active' => true]);
        $qa->assignRole('QA');

        $pm = User::factory()->create(['is_active' => true]);
        $pm->assignRole('Project Manager');

        $proj = Project::create(['name' => 'Sistem Gudang', 'platform' => 'Web']);
        $sev = Severity::create(['level' => 'Minor', 'description' => 'Minor']);

        $bug = Bug::create([
            'project_id' => $proj->id,
            'severity_id' => $sev->id,
            'priority_id' => null,
            'assignee_id' => null,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0',
            'title' => 'Bug B',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Testing',
        ]);

        $this->actingAs($qa)
            ->post(route('qa.bugs.approve', $bug))
            ->assertRedirect();

        $this->assertDatabaseHas('bugs', [
            'id' => $bug->id,
            'status' => 'Resolved',
        ]);

        $this->assertDatabaseHas('bug_status_histories', [
            'bug_id' => $bug->id,
            'old_status' => 'Testing',
            'new_status' => 'Resolved',
            'user_id' => $qa->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $pm->id,
            'related_id' => $bug->id,
            'type' => 'BugDone',
        ]);
    }

    public function test_qa_reject_writes_bug_rejected_notification_to_assignee_when_reason_present(): void
    {
        Role::create(['name' => 'QA', 'guard_name' => 'web']);
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);

        $qa = User::factory()->create(['is_active' => true]);
        $qa->assignRole('QA');

        $dev = User::factory()->create(['is_active' => true]);
        $dev->assignRole('Programmer');

        $proj = Project::create(['name' => 'Sistem Gudang', 'platform' => 'Web']);
        $sev = Severity::create(['level' => 'Minor', 'description' => 'Minor']);

        $bug = Bug::create([
            'project_id' => $proj->id,
            'severity_id' => $sev->id,
            'priority_id' => null,
            'assignee_id' => $dev->id,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0',
            'title' => 'Bug C',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Testing',
        ]);

        $this->actingAs($qa)
            ->post(route('qa.bugs.reject', $bug), ['reason' => 'Repro steps mismatch'])
            ->assertRedirect();

        $this->assertDatabaseHas('bugs', [
            'id' => $bug->id,
            'status' => 'In Progress',
        ]);

        $this->assertDatabaseHas('bug_status_histories', [
            'bug_id' => $bug->id,
            'old_status' => 'Testing',
            'new_status' => 'In Progress',
            'user_id' => $qa->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $dev->id,
            'related_id' => $bug->id,
            'type' => 'BugRejected',
        ]);
    }

    public function test_qa_reject_without_reason_still_notifies_assignee_and_returns_to_in_progress(): void
    {
        Role::create(['name' => 'QA', 'guard_name' => 'web']);
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);

        $qa = User::factory()->create(['is_active' => true]);
        $qa->assignRole('QA');

        $dev = User::factory()->create(['is_active' => true]);
        $dev->assignRole('Programmer');

        $proj = Project::create(['name' => 'Sistem Gudang', 'platform' => 'Web']);
        $sev = Severity::create(['level' => 'Minor', 'description' => 'Minor']);

        $bug = Bug::create([
            'project_id' => $proj->id,
            'severity_id' => $sev->id,
            'priority_id' => null,
            'assignee_id' => $dev->id,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0',
            'title' => 'Bug D',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Testing',
        ]);

        $this->actingAs($qa)
            ->post(route('qa.bugs.reject', $bug))
            ->assertRedirect();

        $this->assertDatabaseHas('bugs', [
            'id' => $bug->id,
            'status' => 'In Progress',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $dev->id,
            'related_id' => $bug->id,
            'type' => 'BugRejected',
        ]);
    }
}
