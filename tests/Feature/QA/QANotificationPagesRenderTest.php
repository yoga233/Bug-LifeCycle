<?php

namespace Tests\Feature\QA;

use App\Models\Bug;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Role;
use App\Models\Severity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QANotificationPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_qa_can_open_notifications_page_and_see_items(): void
    {
        Role::create(['name' => 'QA', 'guard_name' => 'web']);

        $qa = User::factory()->create(['is_active' => true]);
        $qa->assignRole('QA');

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
            'title' => 'Bug QA Notification',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Testing',
        ]);

        Notification::create([
            'user_id' => $qa->id,
            'related_id' => $bug->id,
            'type' => 'BugStatusChanged',
            'message' => 'Bug siap diuji QA',
            'is_read' => false,
            'created_at' => now(),
        ]);

        $this->actingAs($qa)
            ->get(route('qa.notifications'))
            ->assertOk()
            ->assertSee('Notifikasi')
            ->assertSee('Bug siap diuji QA');
    }

    public function test_qa_can_mark_all_notifications_as_read(): void
    {
        Role::create(['name' => 'QA', 'guard_name' => 'web']);

        $qa = User::factory()->create(['is_active' => true]);
        $qa->assignRole('QA');

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
            'title' => 'Bug QA Notification Read',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Testing',
        ]);

        $n1 = Notification::create([
            'user_id' => $qa->id,
            'related_id' => $bug->id,
            'type' => 'BugStatusChanged',
            'message' => 'Notif 1',
            'is_read' => false,
            'created_at' => now(),
        ]);

        $n2 = Notification::create([
            'user_id' => $qa->id,
            'related_id' => $bug->id,
            'type' => 'BugStatusChanged',
            'message' => 'Notif 2',
            'is_read' => false,
            'created_at' => now(),
        ]);

        $this->actingAs($qa)
            ->post(route('qa.notifications.markAllRead'))
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', ['id' => $n1->id, 'is_read' => true]);
        $this->assertDatabaseHas('notifications', ['id' => $n2->id, 'is_read' => true]);
    }

    public function test_qa_can_delete_single_notification(): void
    {
        Role::create(['name' => 'QA', 'guard_name' => 'web']);

        $qa = User::factory()->create(['is_active' => true]);
        $qa->assignRole('QA');

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
            'title' => 'Bug QA Notification Delete',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Testing',
        ]);

        $notification = Notification::create([
            'user_id' => $qa->id,
            'related_id' => $bug->id,
            'type' => 'BugStatusChanged',
            'message' => 'Notif hapus QA',
            'is_read' => false,
            'created_at' => now(),
        ]);

        $this->actingAs($qa)
            ->delete(route('qa.notifications.destroy', $notification))
            ->assertRedirect();

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }
}
