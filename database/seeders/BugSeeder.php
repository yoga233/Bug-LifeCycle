<?php

namespace Database\Seeders;

use App\Models\Bug;
use App\Models\BugStatusHistory;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Severity;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BugSeeder extends Seeder
{
    private int $dummyCounter = 1;

    public function run(): void
    {
        $oldDummyBugIds = Bug::withTrashed()
            ->where(function ($query) {
                $query
                    ->where('guest_email', 'like', 'guest%@example.test')
                    ->orWhere('guest_email', 'like', 'dummy.reporter.%@buglife.local')
                    ->orWhere('title', 'like', '[Dummy %]%');
            })
            ->pluck('id')
            ->all();

        if (! empty($oldDummyBugIds)) {
            BugStatusHistory::query()->whereIn('bug_id', $oldDummyBugIds)->delete();
            Bug::withTrashed()->whereIn('id', $oldDummyBugIds)->forceDelete();
        }

        $projectIds = Project::query()->pluck('id')->all();
        $severityIds = Severity::query()->pluck('id')->all();
        $priorities = Priority::query()->get(['id', 'sla_hours']);
        $programmerIds = User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Programmer'))
            ->pluck('id')
            ->all();

        if (empty($projectIds) || empty($severityIds) || empty($programmerIds) || $priorities->isEmpty()) {
            return;
        }

        $statusPlan = array_merge(
            array_fill(0, 4, 'Reported'),
            array_fill(0, 7, 'Assigned'),
            array_fill(0, 4, 'In Progress'),
            array_fill(0, 3, 'Testing'),
            array_fill(0, 2, 'Rejected'),
            array_fill(0, 130, 'Resolved'),
        );

        shuffle($statusPlan);

        // Tambah 5 bug dummy tanpa prioritas (priority_id null)
        for ($i = 0; $i < 5; $i++) {
            $status = $i < 3 ? 'Reported' : 'Assigned';
            $this->seedBug($status, $projectIds, $severityIds, $priorities, $programmerIds, true);
        }

        foreach ($statusPlan as $status) {
            $this->seedBug($status, $projectIds, $severityIds, $priorities, $programmerIds);
        }
    }

    // Tambah parameter $noPriority
    private function seedBug(string $status, array $projectIds, array $severityIds, Collection $priorities, array $programmerIds, bool $noPriority = false): void
    {
        $dummyNumber = $this->nextDummyNumber();
        $priority = $priorities->random();
        $assigneeId = $status === 'Reported' ? null : $programmerIds[($dummyNumber - 1) % count($programmerIds)];
        $profile = $this->reporterProfile($dummyNumber);
        $scenario = $this->bugScenario($dummyNumber);

        [$createdAt, $transitionTimes, $finalUpdatedAt] = $this->timelineForStatus($status, $priority);

        $title = $scenario['title'];
        $description = $this->description($scenario, $profile, $status === 'Resolved');

        // If reporter is not from Indonesia use English text when available
        if (strtolower($profile['country']) !== 'indonesia') {
            if (! empty($scenario['title_en'])) {
                $title = $scenario['title_en'];
            }
            $description = $this->description($scenario, $profile, $status === 'Resolved', 'en');
        }

        $bug = Bug::create([
            'project_id' => $projectIds[($dummyNumber - 1) % count($projectIds)],
            'severity_id' => Arr::random($severityIds),
            'priority_id' => $noPriority ? null : (int) $priority->id,
            'assignee_id' => $assigneeId,
            'guest_name' => $profile['name'],
            'guest_email' => 'dummy.reporter.'.str_pad((string) $dummyNumber, 3, '0', STR_PAD_LEFT).'@buglife.local',
            'guest_version' => $scenario['version'],
            'title' => $title,
            'description' => $description,
            'frequency' => $scenario['frequency'],
            'status' => $status,
            'created_at' => $createdAt,
            'updated_at' => $finalUpdatedAt,
        ]);

        $this->seedHistory($bug, $assigneeId, $createdAt, $transitionTimes);
    }

    private function timelineForStatus(string $status, Priority $priority): array
    {
        $now = Carbon::create(2026, 5, 3, fake()->numberBetween(8, 20), fake()->numberBetween(0, 59), 0);
        $start = Carbon::create(2026, 1, 1, 8, 0, 0);
        $slaHours = max(1, (int) ($priority->sla_hours ?? 24));
        $transitionTimes = [];

        // Status yang belum selesai: Reported, Assigned, In Progress, Testing
        $unfinished = in_array($status, ['Reported', 'Assigned', 'In Progress', 'Testing']);
        if ($unfinished) {
            // Buat rentang 1-14 hari terakhir
            $createdAt = $now->copy()->subDays(fake()->numberBetween(0, 13))->setTime(fake()->numberBetween(8, 20), fake()->numberBetween(0, 59), 0);
        } else {
            // Selesai: random antara 1 Jan - 3 Mei 2026
            $createdAt = $start->copy()->addDays(fake()->numberBetween(0, $now->diffInDays($start)))->setTime(fake()->numberBetween(8, 20), fake()->numberBetween(0, 59), 0);
        }

        if ($status === 'Reported') {
            $final = $unfinished ? $createdAt->copy()->addMinutes(fake()->numberBetween(5, 90)) : $createdAt->copy()->addMinutes(fake()->numberBetween(5, 90));
            if ($final->gt($now)) {
                $final = $now->copy()->subMinutes(fake()->numberBetween(1, 60));
            }
            return [$createdAt, $transitionTimes, $final];
        }

        $assignedAt = $createdAt->copy()->addMinutes(fake()->numberBetween(20, 360));
        if ($assignedAt->gt($now)) {
            $assignedAt = $now->copy()->subMinutes(fake()->numberBetween(1, 120));
        }
        $transitionTimes['Assigned'] = $assignedAt;

        if ($status === 'Assigned') {
            return [$createdAt, $transitionTimes, $assignedAt];
        }

        $inProgressAt = $assignedAt->copy()->addMinutes(fake()->numberBetween(45, 540));
        if ($inProgressAt->gt($now)) {
            $inProgressAt = $now->copy()->subMinutes(fake()->numberBetween(1, 180));
        }
        $transitionTimes['In Progress'] = $inProgressAt;

        if ($status === 'In Progress' || $status === 'Rejected') {
            $updatedAt = $inProgressAt->copy()->addHours(fake()->numberBetween(1, min(18, max(1, $slaHours))));
            if ($updatedAt->gt($now)) {
                $updatedAt = $now->copy()->subMinutes(fake()->numberBetween(1, 120));
            }
            if ($status === 'Rejected') {
                $transitionTimes['Rejected'] = $updatedAt;
            }
            return [$createdAt, $transitionTimes, $updatedAt];
        }

        $testingAt = $inProgressAt->copy()->addHours(fake()->numberBetween(1, max(2, min(24, $slaHours))));
        if ($testingAt->gt($now)) {
            $testingAt = $now->copy()->subMinutes(fake()->numberBetween(5, 180));
        }
        $transitionTimes['Testing'] = $testingAt;

        if ($status === 'Testing') {
            return [$createdAt, $transitionTimes, $testingAt];
        }

        $isOnTime = fake()->numberBetween(1, 100) <= 72;
        $actualMinutes = $isOnTime
            ? fake()->numberBetween(max(60, (int) ($slaHours * 18)), max(90, (int) ($slaHours * 58)))
            : fake()->numberBetween(($slaHours * 60) + 30, ($slaHours * 60) + fake()->numberBetween(180, 960));

        $resolvedAt = $createdAt->copy()->addMinutes($actualMinutes);
        if ($resolvedAt->gt($now)) {
            $resolvedAt = $now->copy()->subMinutes(fake()->numberBetween(1, 120));
        }
        if ($testingAt->gte($resolvedAt)) {
            $testingAt = $resolvedAt->copy()->subMinutes(fake()->numberBetween(15, 90));
            $transitionTimes['Testing'] = $testingAt;
        }
        $transitionTimes['Resolved'] = $resolvedAt;

        return [$createdAt, $transitionTimes, $resolvedAt];
    }

    private function seedHistory(Bug $bug, ?int $assigneeId, Carbon $createdAt, array $transitionTimes): void
    {
        BugStatusHistory::create([
            'bug_id' => $bug->id,
            'user_id' => null,
            'old_status' => 'Reported',
            'new_status' => 'Reported',
            'changed_at' => $createdAt,
        ]);

        $flow = ['Assigned' => 'Reported', 'In Progress' => 'Assigned', 'Testing' => 'In Progress', 'Rejected' => 'In Progress', 'Resolved' => 'Testing'];

        foreach ($transitionTimes as $newStatus => $changedAt) {
            BugStatusHistory::create([
                'bug_id' => $bug->id,
                'user_id' => $assigneeId,
                'old_status' => $flow[$newStatus] ?? 'Reported',
                'new_status' => $newStatus,
                'changed_at' => $changedAt,
            ]);
        }
    }

    private function reporterProfile(int $number): array
    {
        $reporters = [
            ['name' => 'Ayu Lestari', 'country' => 'Indonesia', 'city' => 'Bandung'],
            ['name' => 'Rizky Pratama', 'country' => 'Indonesia', 'city' => 'Jakarta'],
            ['name' => 'Dewi Anggraini', 'country' => 'Indonesia', 'city' => 'Surabaya'],
            ['name' => 'Bagas Nugroho', 'country' => 'Indonesia', 'city' => 'Yogyakarta'],
            ['name' => 'Siti Rahmawati', 'country' => 'Indonesia', 'city' => 'Makassar'],
            ['name' => 'Michael Chen', 'country' => 'Singapore', 'city' => 'Singapore'],
            ['name' => 'Sarah Johnson', 'country' => 'United States', 'city' => 'Austin'],
            ['name' => 'Emily Carter', 'country' => 'United Kingdom', 'city' => 'London'],
            ['name' => 'Hiroshi Tanaka', 'country' => 'Japan', 'city' => 'Tokyo'],
            ['name' => 'Priya Sharma', 'country' => 'India', 'city' => 'Bengaluru'],
            ['name' => 'Luca Rossi', 'country' => 'Italy', 'city' => 'Milan'],
            ['name' => 'Sophie Martin', 'country' => 'France', 'city' => 'Lyon'],
            ['name' => 'Daniel Kim', 'country' => 'South Korea', 'city' => 'Seoul'],
            ['name' => 'Nur Aisyah', 'country' => 'Malaysia', 'city' => 'Kuala Lumpur'],
            ['name' => 'Carlos Mendoza', 'country' => 'Spain', 'city' => 'Madrid'],
        ];
        return $reporters[($number - 1) % count($reporters)];
    }

    private function bugScenario(int $number): array
    {
        $scenarios = [
            [
                'title' => 'Pembayaran berhasil tetapi invoice tidak muncul',
                'title_en' => 'Payment succeeds but invoice does not appear',
                'frequency' => 'Often',
                'version' => 'v2.4.1',
                'module' => 'Checkout',
                'module_en' => 'Checkout',
                'impact' => 'pelanggan tidak bisa mengunduh bukti pembayaran setelah transaksi sukses',
                'impact_en' => 'customers cannot download payment proof after a successful transaction',
            ],
            [
                'title' => 'Filter tanggal laporan menampilkan data bulan sebelumnya',
                'title_en' => 'Report date filter shows previous month data',
                'frequency' => 'Sometimes',
                'version' => 'v3.1.0',
                'module' => 'Reporting',
                'module_en' => 'Reporting',
                'impact' => 'tim operasional harus ekspor ulang laporan secara manual',
                'impact_en' => 'operations team must re-export reports manually',
            ],
            [
                'title' => 'Notifikasi email assignment terlambat terkirim',
                'title_en' => 'Assignment email notification delivered late',
                'frequency' => 'Rare',
                'version' => 'v1.9.8',
                'module' => 'Notification',
                'module_en' => 'Notification',
                'impact' => 'programmer baru mengetahui tugas setelah membuka dashboard',
                'impact_en' => 'programmers learn about tasks only after opening the dashboard',
            ],
            [
                'title' => 'Tombol simpan profil tidak aktif setelah mengganti nomor telepon',
                'title_en' => 'Save profile button inactive after changing phone number',
                'frequency' => 'Often',
                'version' => 'v2.8.3',
                'module' => 'User Profile',
                'module_en' => 'User Profile',
                'impact' => 'pengguna tidak dapat memperbarui data kontak terbaru',
                'impact_en' => 'users cannot update their latest contact information',
            ],
            [
                'title' => 'Halaman dashboard lambat saat memuat kartu metrik',
                'title_en' => 'Dashboard page slow when loading metric cards',
                'frequency' => 'Always',
                'version' => 'v4.0.2',
                'module' => 'Dashboard',
                'module_en' => 'Dashboard',
                'impact' => 'manajer proyek menunggu terlalu lama sebelum melihat ringkasan pekerjaan',
                'impact_en' => 'project managers wait too long before seeing work summaries',
            ],
            [
                'title' => 'Upload lampiran gagal untuk file PDF berukuran kecil',
                'title_en' => 'Attachment upload fails for small PDF files',
                'frequency' => 'Sometimes',
                'version' => 'v2.2.7',
                'module' => 'Attachment',
                'module_en' => 'Attachment',
                'impact' => 'bukti masalah tidak bisa dilampirkan pada tiket',
                'impact_en' => 'evidence cannot be attached to tickets',
            ],
            [
                'title' => 'Status tiket tidak berubah setelah QA menyetujui hasil testing',
                'title_en' => 'Ticket status not updated after QA approves testing results',
                'frequency' => 'Rare',
                'version' => 'v3.3.5',
                'module' => 'QA Workflow',
                'module_en' => 'QA Workflow',
                'impact' => 'tiket yang sudah validasi masih terlihat berada di tahap pengujian',
                'impact_en' => 'validated tickets still appear in testing stage',
            ],
            [
                'title' => "Pencarian tiket gagal menemukan nama pelapor dengan apostrof",
                'title_en' => 'Ticket search fails to find reporter name with apostrophe',
                'frequency' => 'Sometimes',
                'version' => 'v1.7.4',
                'module' => 'Issue Search',
                'module_en' => 'Issue Search',
                'impact' => 'support kesulitan melacak laporan dari pelanggan tertentu',
                'impact_en' => 'support has difficulty tracing reports from certain customers',
            ],
            [
                'title' => 'Dropdown prioritas tertutup ketika halaman di-scroll',
                'title_en' => 'Priority dropdown closes when page is scrolled',
                'frequency' => 'Often',
                'version' => 'v2.6.6',
                'module' => 'Priority Management',
                'module_en' => 'Priority Management',
                'impact' => 'PM harus memilih prioritas berulang kali saat layar kecil',
                'impact_en' => "PM must reselect priority repeatedly on small screens",
            ],
            [
                'title' => 'Grafik kinerja programmer tidak mengikuti rentang tanggal',
                'title_en' => 'Programmer performance chart does not follow date range',
                'frequency' => 'Always',
                'version' => 'v3.5.1',
                'module' => 'Performance Analytics',
                'module_en' => 'Performance Analytics',
                'impact' => 'evaluasi SLA menjadi tidak akurat untuk periode tertentu',
                'impact_en' => 'SLA evaluations become inaccurate for certain periods',
            ],
        ];
        return $scenarios[($number - 1) % count($scenarios)];
    }

    private function description(array $scenario, array $profile, bool $isResolved, string $lang = 'id'): string
    {
        $resolutionNoteId = 'Tim sudah memperbaiki akar masalah dan pelapor mengonfirmasi fitur kembali berjalan normal.';
        $resolutionNoteEn = 'The team fixed the root cause and the reporter confirmed the feature is working again.';

        $resolutionNote = $lang === 'en' ? $resolutionNoteEn : $resolutionNoteId;

        if ($lang === 'en') {
            return implode("\n\n", [
                "Reporter {$profile['name']} from {$profile['city']}, {$profile['country']} reported an issue in the {$scenario['module_en']} module.",
                "Business impact: {$scenario['impact_en']}. This was observed on application version {$scenario['version']} with frequency {$scenario['frequency']}.",
                "Steps to reproduce: log in as an active user, open the {$scenario['module_en']} module, perform the main flow, and observe the unexpected behavior.",
                $resolutionNote,
            ]);
        }

        return implode("\n\n", [
            "Pelapor {$profile['name']} dari {$profile['city']}, {$profile['country']} melaporkan kendala pada modul {$scenario['module']}.",
            "Dampak bisnis: {$scenario['impact']}. Kejadian ini terdeteksi pada versi aplikasi {$scenario['version']} dengan frekuensi {$scenario['frequency']}.",
            "Langkah reproduksi: login sebagai pengguna aktif, buka modul {$scenario['module']}, lakukan proses utama seperti biasa, lalu perhatikan perilaku sistem yang tidak sesuai ekspektasi.",
            $resolutionNote,
        ]);
    }

    private function nextDummyNumber(): int
    {
        return $this->dummyCounter++;
    }
}
