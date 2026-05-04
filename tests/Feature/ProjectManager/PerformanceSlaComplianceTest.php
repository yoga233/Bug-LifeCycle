<?php

namespace Tests\Feature\ProjectManager;

use App\Models\Bug;
use App\Models\BugStatusHistory;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Role;
use App\Models\Severity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceSlaComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_pm_kinerja_shows_sla_compliance_only_when_programmer_filtered(): void
    {
        Role::create(['name' => 'Project Manager', 'guard_name' => 'web']);
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);

        $pm = User::factory()->create(['is_active' => true]);
        $pm->assignRole('Project Manager');

        $programmer = User::factory()->create(['is_active' => true]);
        $programmer->assignRole('Programmer');

        $project = Project::create(['name' => 'Demo', 'platform' => 'Web']);
        $severity = Severity::create(['level' => 'Minor', 'description' => 'Minor']);
        $priority = Priority::create(['level' => 'High', 'sla_hours' => 8]);

        $bug = Bug::create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $programmer->id,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0',
            'title' => 'Bug SLA Tepat Waktu',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Resolved',
        ]);

        Bug::query()->whereKey($bug->id)->update([
            'created_at' => now()->subHours(7),
            'updated_at' => now()->subHours(1),
        ]);

        BugStatusHistory::create([
            'bug_id' => $bug->id,
            'user_id' => $programmer->id,
            'old_status' => 'Testing',
            'new_status' => 'Resolved',
            'changed_at' => now()->subHours(1),
        ]);

        $this->actingAs($pm)
            ->get(route('pm.kinerja'))
            ->assertOk()
            ->assertDontSee('Ringkasan SLA:');

        $filteredResponse = $this->actingAs($pm)
            ->get(route('pm.kinerja', ['assignee_id' => $programmer->id]));

        $filteredResponse
            ->assertOk()
            ->assertSee('Ringkasan SLA: '.$programmer->name)
            ->assertSee('Status SLA: Aman')
            ->assertSee('Semua tiket terukur selesai tepat SLA.')
            ->assertSee('Diurutkan berdasarkan waktu penyelesaian terbaru')
            ->assertSee('SLA/Aktual (jam):')
            ->assertSee('Garis biru = target SLA')
            ->assertSee('Hijau = tepat SLA', false)
            ->assertSee('Merah = lewat SLA', false)
            ->assertSee('Tepat SLA');

        $this->assertMatchesRegularExpression('/8\/(6|7)/', $filteredResponse->getContent());
        $this->assertMatchesRegularExpression('/Lebih cepat\s+\d+\s+jam/', $filteredResponse->getContent());
    }

    public function test_pm_kinerja_sla_compliance_respects_time_filter_and_all_time(): void
    {
        Role::create(['name' => 'Project Manager', 'guard_name' => 'web']);
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);

        $pm = User::factory()->create(['is_active' => true]);
        $pm->assignRole('Project Manager');

        $programmer = User::factory()->create(['is_active' => true]);
        $programmer->assignRole('Programmer');

        $project = Project::create(['name' => 'Demo', 'platform' => 'Web']);
        $severity = Severity::create(['level' => 'Minor', 'description' => 'Minor']);
        $priority = Priority::create(['level' => 'High', 'sla_hours' => 8]);

        $recentBug = Bug::create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $programmer->id,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0',
            'title' => 'Bug SLA Terlambat',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Resolved',
        ]);

        Bug::query()->whereKey($recentBug->id)->update([
            'created_at' => now()->subHours(12),
            'updated_at' => now()->subMinutes(10),
        ]);

        BugStatusHistory::create([
            'bug_id' => $recentBug->id,
            'user_id' => $programmer->id,
            'old_status' => 'Testing',
            'new_status' => 'Resolved',
            'changed_at' => now()->subMinutes(10),
        ]);

        $oldBug = Bug::create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $programmer->id,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0',
            'title' => 'Bug SLA Lama Tepat',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Resolved',
        ]);

        Bug::query()->whereKey($oldBug->id)->update([
            'created_at' => now()->subDays(12)->subHours(6),
            'updated_at' => now()->subDays(12),
        ]);

        BugStatusHistory::create([
            'bug_id' => $oldBug->id,
            'user_id' => $programmer->id,
            'old_status' => 'Testing',
            'new_status' => 'Resolved',
            'changed_at' => now()->subDays(12),
        ]);

        $from = now()->subDay()->toDateString();
        $to = now()->toDateString();

        $this->actingAs($pm)
            ->get(route('pm.kinerja', [
                'assignee_id' => $programmer->id,
                'from' => $from,
                'to' => $to,
            ]))
            ->assertOk()
            ->assertSee('Diurutkan berdasarkan waktu penyelesaian terbaru')
            ->assertSee('Garis biru = target SLA')
            ->assertSee('Hijau = tepat SLA', false)
            ->assertSee('Merah = lewat SLA', false)
            ->assertSee('SLA Compliance')
            ->assertSee('Status SLA: Kritis')
            ->assertSee('Ada 1 tiket lewat SLA, perlu perhatian cepat.')
            ->assertSee('dari 1 tiket terukur')
            ->assertSee('0.0%')
            ->assertSee('8/12')
            ->assertSee('Terlambat 4 jam')
            ->assertSee('Terlambat SLA')
            ->assertDontSee('Bug SLA Lama Tepat');

        $this->actingAs($pm)
            ->get(route('pm.kinerja', [
                'assignee_id' => $programmer->id,
                'all_time' => 1,
                'from' => $from,
                'to' => $to,
            ]))
            ->assertOk()
            ->assertSee('Semua waktu')
            ->assertSee('Status SLA: Kritis')
            ->assertSee('dari 2 tiket terukur')
            ->assertSee('50.0%')
            ->assertSee('Bug SLA Lama Tepat');
    }

    public function test_pm_kinerja_sla_chart_items_are_sorted_by_latest_resolved_time(): void
    {
        Role::create(['name' => 'Project Manager', 'guard_name' => 'web']);
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);

        $pm = User::factory()->create(['is_active' => true]);
        $pm->assignRole('Project Manager');

        $programmer = User::factory()->create(['is_active' => true]);
        $programmer->assignRole('Programmer');

        $project = Project::create(['name' => 'Demo', 'platform' => 'Web']);
        $severity = Severity::create(['level' => 'Minor', 'description' => 'Minor']);
        $priority = Priority::create(['level' => 'High', 'sla_hours' => 8]);

        $olderLateBug = Bug::create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $programmer->id,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0',
            'title' => 'Bug Lama Terlambat',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Resolved',
        ]);

        Bug::query()->whereKey($olderLateBug->id)->update([
            'created_at' => now()->subDays(4)->subHours(12),
            'updated_at' => now()->subDays(4),
        ]);

        BugStatusHistory::create([
            'bug_id' => $olderLateBug->id,
            'user_id' => $programmer->id,
            'old_status' => 'Testing',
            'new_status' => 'Resolved',
            'changed_at' => now()->subDays(4),
        ]);

        $newerOnTimeBug = Bug::create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $programmer->id,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'guest_version' => '1.0',
            'title' => 'Bug Baru Tepat',
            'description' => 'desc',
            'frequency' => 'once',
            'status' => 'Resolved',
        ]);

        Bug::query()->whereKey($newerOnTimeBug->id)->update([
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHour(),
        ]);

        BugStatusHistory::create([
            'bug_id' => $newerOnTimeBug->id,
            'user_id' => $programmer->id,
            'old_status' => 'Testing',
            'new_status' => 'Resolved',
            'changed_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($pm)
            ->get(route('pm.kinerja', [
                'assignee_id' => $programmer->id,
                'all_time' => 1,
            ]));

        $response
            ->assertOk()
            ->assertSee('Diurutkan berdasarkan waktu penyelesaian terbaru');

        $content = $response->getContent();

        $this->assertSame(1, preg_match("/data-sla-ranked-items='([^']*)'/", $content, $matches));

        $rankedItems = json_decode($matches[1] ?? '[]', true);

        $this->assertIsArray($rankedItems);
        $this->assertCount(2, $rankedItems);
        $this->assertSame('Bug Baru Tepat', $rankedItems[0]['title'] ?? null);
        $this->assertSame('Bug Lama Terlambat', $rankedItems[1]['title'] ?? null);
    }
}
