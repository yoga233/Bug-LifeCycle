<?php

namespace Tests\Feature\ProjectManager;

use App\Models\Bug;
use App\Models\Project;
use App\Models\Role;
use App\Models\Severity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssueCommentAjaxTest extends TestCase
{
    use RefreshDatabase;

    public function test_pm_can_post_comment_via_ajax_and_get_json_response(): void
    {
        Role::create(['name' => 'Project Manager', 'guard_name' => 'web']);
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);

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
            'title' => 'Bug Comment',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Reported',
        ]);

        $this->actingAs($pm)
            ->postJson(route('pm.issues.comments.store', $bug), ['content' => 'Test komentar via ajax'])
            ->assertCreated()
            ->assertJsonPath('comment.content', 'Test komentar via ajax')
            ->assertJsonPath('comment.user_name', $pm->name);

        $this->assertDatabaseHas('comments', [
            'bug_id' => $bug->id,
            'user_id' => $pm->id,
            'content' => 'Test komentar via ajax',
        ]);
    }
}
