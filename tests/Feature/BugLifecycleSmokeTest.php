<?php

namespace Tests\Feature;

use App\Models\Bug;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Role;
use App\Models\Severity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BugLifecycleSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_bug_lifecycle_models_and_spatie_roles_work_together(): void
    {
        // 1) Buat Role dulu
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);

        // 2) Buat User (Programmer)
        $dev = User::create([
            'name' => 'Budi Programmer',
            'email' => 'budi@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        // 3) Tes Spatie assign role
        $dev->assignRole('Programmer');
        $this->assertTrue($dev->hasRole('Programmer'));

        // 4) Buat master data
        $proj = Project::create(['name' => 'Sistem Gudang', 'platform' => 'Web']);
        $sev = Severity::create(['level' => 'Critical', 'description' => 'Mati total']);
        $prio = Priority::create(['level' => 'High', 'sla_hours' => 24]);

        // 5) Buat bug
        $bug = Bug::create([
            'project_id' => $proj->id,
            'severity_id' => $sev->id,
            'priority_id' => $prio->id,
            'assignee_id' => $dev->id,
            'guest_name' => 'Klien A',
            'guest_email' => 'klien@gmail.com',
            'guest_version' => 'v1.0',
            'title' => 'Tombol Login Error',
            'description' => 'Tidak bisa klik login',
            'frequency' => 'Always',
            'status' => 'Assigned',
        ]);

        // 6) Cek relasi
        $this->assertSame('Sistem Gudang', $bug->project->name);
        $this->assertSame('Budi Programmer', $bug->assignee->name);
        $this->assertCount(1, $dev->bugs);
        $this->assertSame($bug->id, $dev->bugs->first()->id);
    }
}
