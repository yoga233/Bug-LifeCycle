<?php

namespace Tests\Feature\ProjectManager;

use App\Models\Bug;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Role;
use App\Models\Severity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectManagerPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_pm_can_open_overview_and_issues_pages(): void
    {
        Role::create(['name' => 'Project Manager', 'guard_name' => 'web']);

        $pm = User::factory()->create(['is_active' => true]);
        $pm->assignRole('Project Manager');

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
            'title' => 'Bug Project Manager Page',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Reported',
        ]);

        $this->actingAs($pm)->get(route('pm.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Project Manager');

        $this->actingAs($pm)->get(route('pm.issues.index'))
            ->assertOk()
            ->assertSee('Manajemen Bug')
            ->assertSee('Bug Project Manager Page');

        $this->actingAs($pm)->get(route('pm.issues.show', $bug))
            ->assertOk()
            ->assertSee('Bug Project Manager Page');

        $this->actingAs($pm)->get(route('pm.notifications'))
            ->assertOk()
            ->assertSee('Notifikasi');
    }

    public function test_pm_can_delete_single_notification(): void
    {
        Role::create(['name' => 'Project Manager', 'guard_name' => 'web']);

        $pm = User::factory()->create(['is_active' => true]);
        $pm->assignRole('Project Manager');

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
            'title' => 'Bug PM Notification Delete',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Reported',
        ]);

        $notification = Notification::create([
            'user_id' => $pm->id,
            'related_id' => $bug->id,
            'type' => 'BugReported',
            'message' => 'Notif hapus PM',
            'is_read' => false,
            'created_at' => now(),
        ]);

        $this->actingAs($pm)
            ->delete(route('pm.notifications.destroy', $notification))
            ->assertRedirect();

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }
}
