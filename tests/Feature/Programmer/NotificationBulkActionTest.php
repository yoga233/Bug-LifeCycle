<?php

namespace Tests\Feature\Programmer;

use App\Models\Bug;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Role;
use App\Models\Severity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationBulkActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_programmer_can_mark_all_notifications_as_read(): void
    {
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);

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
            'title' => 'Bug Notif',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Assigned',
        ]);

        $n1 = Notification::create([
            'user_id' => $dev->id,
            'related_id' => $bug->id,
            'type' => 'BugAssigned',
            'message' => 'Assigned',
            'is_read' => false,
            'created_at' => now(),
        ]);

        $n2 = Notification::create([
            'user_id' => $dev->id,
            'related_id' => $bug->id,
            'type' => 'BugStatusChanged',
            'message' => 'Changed',
            'is_read' => false,
            'created_at' => now(),
        ]);

        $n3 = Notification::create([
            'user_id' => $dev->id,
            'related_id' => $bug->id,
            'type' => 'BugRejected',
            'message' => 'Rejected',
            'is_read' => true,
            'created_at' => now(),
        ]);

        $this->actingAs($dev)
            ->post(route('programmer.notifications.markAllRead'))
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', ['id' => $n1->id, 'is_read' => true]);
        $this->assertDatabaseHas('notifications', ['id' => $n2->id, 'is_read' => true]);
        $this->assertDatabaseHas('notifications', ['id' => $n3->id, 'is_read' => true]);
    }

    public function test_programmer_can_delete_single_notification(): void
    {
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);

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
            'title' => 'Bug Notif Delete',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Assigned',
        ]);

        $notification = Notification::create([
            'user_id' => $dev->id,
            'related_id' => $bug->id,
            'type' => 'BugAssigned',
            'message' => 'Delete me',
            'is_read' => false,
            'created_at' => now(),
        ]);

        $this->actingAs($dev)
            ->delete(route('programmer.notifications.destroy', $notification))
            ->assertRedirect();

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }
}
