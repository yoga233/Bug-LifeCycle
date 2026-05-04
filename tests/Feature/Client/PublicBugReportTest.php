<?php

namespace Tests\Feature\Client;

use App\Models\Bug;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Role;
use App\Models\Severity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\BugReportedTicketMail;
use Tests\TestCase;

class PublicBugReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_bug_report_without_attachments(): void
    {
        Mail::fake();

        Role::create(['name' => 'Project Manager', 'guard_name' => 'web']);
        $pm = User::factory()->create(['is_active' => true]);
        $pm->assignRole('Project Manager');

        $project = Project::create([
            'name' => 'Demo Project',
            'platform' => 'Web',
            'description' => 'Demo',
        ]);

        $severity = Severity::create([
            'level' => 'Critical',
            'description' => 'Critical impact',
        ]);

        $response = $this->post(route('client.report.store'), [
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
            'guest_version' => '1.0.0',
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'title' => 'Submit button broken',
            'description' => 'Button does nothing',
            'reproduction_steps' => "1. Open page\n2. Click submit",
            'frequency' => 'frequent',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('bugs', 1);
        $this->assertDatabaseCount('attachments', 0);
        $this->assertDatabaseCount('bug_status_histories', 1);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $pm->id,
            'type' => 'BugReported',
        ]);

        $bug = Bug::query()->firstOrFail();
        $this->assertSame('Reported', $bug->status);
        $this->assertNull($bug->priority_id);
        $this->assertStringContainsString('Langkah Reproduksi', $bug->description);

        Mail::assertSent(BugReportedTicketMail::class, function (BugReportedTicketMail $mail) use ($bug) {
            return $mail->hasTo($bug->guest_email) && str_contains($mail->ticket, 'BUG-');
        });
    }

    public function test_guest_can_submit_bug_report_with_attachments(): void
    {
        Storage::fake('public');
        Mail::fake();

        Role::create(['name' => 'Project Manager', 'guard_name' => 'web']);
        $pm = User::factory()->create(['is_active' => true]);
        $pm->assignRole('Project Manager');

        $project = Project::create([
            'name' => 'Demo Project',
            'platform' => 'Web',
            'description' => 'Demo',
        ]);

        $severity = Severity::create([
            'level' => 'Major',
            'description' => 'Major impact',
        ]);

        $response = $this->post(route('client.report.store'), [
            'guest_name' => 'Jane Doe',
            'guest_email' => 'jane@example.com',
            'guest_version' => '2.0.0',
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'title' => 'Upload fails',
            'description' => 'Error 500',
            'reproduction_steps' => "1. Open upload page\n2. Upload file",
            'frequency' => 'once',
            'attachments' => [
                // NOTE: do not use ->image() because it requires GD extension.
                UploadedFile::fake()->create('screenshot.jpg', 120, 'image/jpeg'),
                UploadedFile::fake()->create('error.log', 10, 'text/plain'),
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('bugs', 1);
        $this->assertDatabaseCount('attachments', 2);
        $this->assertDatabaseCount('bug_status_histories', 1);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $pm->id,
            'type' => 'BugReported',
        ]);

        $bug = Bug::query()->firstOrFail();
        $attachments = $bug->attachments()->get();

        foreach ($attachments as $att) {
            Storage::disk('public')->assertExists($att->file_path);
        }

        Mail::assertSent(BugReportedTicketMail::class);
    }
}
