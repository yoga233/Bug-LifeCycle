<?php

namespace Tests\Feature\Programmer;

use App\Models\Bug;
use App\Models\Project;
use App\Models\Role;
use App\Models\Severity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgrammerPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_programmer_can_open_dashboard_notifications_kinerja(): void
    {
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);

        $dev = User::factory()->create(['is_active' => true]);
        $dev->assignRole('Programmer');

        $project = Project::create(['name' => 'Demo', 'platform' => 'Web']);
        $severity = Severity::create(['level' => 'Minor', 'description' => 'Minor']);

        Bug::create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => null,
            'assignee_id' => $dev->id,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0',
            'title' => 'Bug Programmer',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Assigned',
        ]);

        $this->actingAs($dev)->get(route('programmer.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Programmer');

        $this->actingAs($dev)->get(route('programmer.notifications'))
            ->assertOk()
            ->assertSee('Notifikasi');

        $this->actingAs($dev)->get(route('programmer.kinerja'))
            ->assertOk()
            ->assertSee('Riwayat Kinerja');
    }

    public function test_dashboard_prioritizes_work_queue_and_shows_status_labels(): void
    {
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);

        $dev = User::factory()->create(['is_active' => true]);
        $dev->assignRole('Programmer');

        $project = Project::create(['name' => 'Demo', 'platform' => 'Web']);
        $severity = Severity::create(['level' => 'Minor', 'description' => 'Minor']);

        // Dibuat lebih dulu (lebih lama) agar memvalidasi bahwa status Assigned tetap di atas
        // meskipun item In Progress dibuat/diupdate lebih baru.
        $assignedTitle = 'Bug Assigned Prioritas Atas';
        $inProgressTitle = 'Bug In Progress Lebih Baru';
        $testingTitle = 'Bug Testing Menunggu QA';
        $rejectedTitle = 'Bug Rejected Dari QA';

        Bug::create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => null,
            'assignee_id' => $dev->id,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0',
            'title' => $assignedTitle,
            'description' => 'desc assigned',
            'frequency' => 'once',
            'status' => 'Assigned',
        ]);

        Bug::create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => null,
            'assignee_id' => $dev->id,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0',
            'title' => $inProgressTitle,
            'description' => 'desc progress',
            'frequency' => 'once',
            'status' => 'In Progress',
        ]);

        Bug::create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => null,
            'assignee_id' => $dev->id,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0',
            'title' => $testingTitle,
            'description' => 'desc testing',
            'frequency' => 'once',
            'status' => 'Testing',
        ]);

        Bug::create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => null,
            'assignee_id' => $dev->id,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0',
            'title' => $rejectedTitle,
            'description' => 'desc rejected',
            'frequency' => 'once',
            'status' => 'Rejected',
        ]);

        $this->actingAs($dev)->get(route('programmer.dashboard'))
            ->assertOk()
            ->assertSeeInOrder([$assignedTitle, $inProgressTitle, $testingTitle, $rejectedTitle])
            ->assertSee('Perlu mulai pengerjaan')
            ->assertSee('Sedang dikerjakan')
            ->assertSee('Menunggu validasi QA')
            ->assertSee('Dikembalikan oleh QA')
            ->assertDontSee('Belum diterima')
            ->assertDontSee('Sudah ditindaklanjuti');
    }
}
