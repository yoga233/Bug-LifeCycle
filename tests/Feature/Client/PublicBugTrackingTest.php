<?php

namespace Tests\Feature\Client;

use App\Models\Bug;
use App\Models\BugStatusHistory;
use App\Models\Project;
use App\Models\Severity;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBugTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracking_shows_error_for_invalid_ticket_format(): void
    {
        $this->get(route('client.tracking', ['ticket' => 'INVALID']))
            ->assertOk()
            ->assertSee('Format tiket tidak valid');
    }

    public function test_tracking_shows_error_for_not_found_ticket(): void
    {
        // Ticket format valid, but bug does not exist
        $ticket = app(TicketService::class)->fromBugId(999999);

        $this->get(route('client.tracking', ['ticket' => $ticket]))
            ->assertOk()
            ->assertSee('Tiket tidak ditemukan');
    }

    public function test_tracking_can_show_bug_from_db(): void
    {
        $project = Project::create([
            'name' => 'Demo Project',
            'platform' => 'Web',
            'description' => 'Demo',
        ]);

        $severity = Severity::create([
            'level' => 'Minor',
            'description' => 'Minor',
        ]);

        $bug = Bug::create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => null,
            'assignee_id' => null,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0.0',
            'title' => 'Example Bug',
            'description' => 'Example',
            'frequency' => 'once',
            'status' => 'Reported',
        ]);

        $ticket = app(TicketService::class)->fromBugId($bug->id);

        $this->get(route('client.tracking', ['ticket' => $ticket]))
            ->assertOk()
            ->assertSee('Example Bug')
            ->assertSee('Demo Project');
    }

    public function test_tracking_shows_returned_status_message_for_unassign_and_qa_return(): void
    {
        $project = Project::create([
            'name' => 'Demo Project',
            'platform' => 'Web',
            'description' => 'Demo',
        ]);

        $severity = Severity::create([
            'level' => 'Minor',
            'description' => 'Minor',
        ]);

        $bug = Bug::create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => null,
            'assignee_id' => null,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0.0',
            'title' => 'Rollback Flow Bug',
            'description' => 'Example',
            'frequency' => 'once',
            'status' => 'In Progress',
        ]);

        BugStatusHistory::create([
            'bug_id' => $bug->id,
            'user_id' => null,
            'old_status' => 'Assigned',
            'new_status' => 'Reported',
            'changed_at' => now()->subHours(2),
        ]);

        BugStatusHistory::create([
            'bug_id' => $bug->id,
            'user_id' => null,
            'old_status' => 'Testing',
            'new_status' => 'In Progress',
            'changed_at' => now()->subHour(),
        ]);

        $ticket = app(TicketService::class)->fromBugId($bug->id);

        $this->get(route('client.tracking', ['ticket' => $ticket]))
            ->assertOk()
            ->assertSee('Penugasan dibatalkan, status dikembalikan ke')
            ->assertSee('Hasil pengujian dikembalikan ke');
    }
}
