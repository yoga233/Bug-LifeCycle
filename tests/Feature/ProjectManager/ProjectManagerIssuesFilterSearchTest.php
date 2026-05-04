<?php

namespace Tests\Feature\ProjectManager;

use App\Models\Bug;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Role;
use App\Models\Severity;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectManagerIssuesFilterSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_pm_can_filter_by_project_status_priority_and_assignee_and_search_by_title_or_ticket(): void
    {
        Role::create(['name' => 'Project Manager', 'guard_name' => 'web']);
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);

        $pm = User::factory()->create(['is_active' => true]);
        $pm->assignRole('Project Manager');

        $dev = User::factory()->create(['is_active' => true, 'name' => 'Dev A']);
        $dev->assignRole('Programmer');

        $projectA = Project::create(['name' => 'Project A', 'platform' => 'Web']);
        $projectB = Project::create(['name' => 'Project B', 'platform' => 'Web']);

        $severity = Severity::create(['level' => 'Minor', 'description' => 'Minor']);
        $priorityHigh = Priority::create(['level' => 'High', 'sla_hours' => 24]);
        $priorityLow = Priority::create(['level' => 'Low', 'sla_hours' => 72]);

        $match = Bug::create([
            'project_id' => $projectA->id,
            'severity_id' => $severity->id,
            'priority_id' => $priorityHigh->id,
            'assignee_id' => $dev->id,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0',
            'title' => 'Login button broken',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Assigned',
        ]);

        $nonMatch = Bug::create([
            'project_id' => $projectB->id,
            'severity_id' => $severity->id,
            'priority_id' => $priorityLow->id,
            'assignee_id' => null,
            'guest_name' => 'Guest',
            'guest_email' => 'guest2@example.com',
            'guest_version' => '1.0',
            'title' => 'Different issue',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Reported',
        ]);

        // 1) filter stack should include only $match
        $this->actingAs($pm)
            ->get(route('pm.issues.index', [
                'project_id' => $projectA->id,
                'status' => 'Assigned',
                'priority_id' => $priorityHigh->id,
                'assignee_id' => $dev->id,
            ]))
            ->assertOk()
            ->assertSee('Login button broken')
            ->assertDontSee('Different issue');

        // 2) search by title
        $this->actingAs($pm)
            ->get(route('pm.issues.index', ['q' => 'Login button']))
            ->assertOk()
            ->assertSee('Login button broken');

        // 3) search by Ticket ID should find the same bug
        $ticket = app(TicketService::class)->fromBugId($match->id);
        $this->actingAs($pm)
            ->get(route('pm.issues.index', ['q' => $ticket]))
            ->assertOk()
            ->assertSee('Login button broken');

        // 4) filter by unassigned
        $this->actingAs($pm)
            ->get(route('pm.issues.index', ['assignee_id' => 'unassigned']))
            ->assertOk()
            ->assertSee('Different issue')
            ->assertDontSee('Login button broken');
    }
}
