<?php

namespace App\Jobs;

use App\Models\GuestBugReport;
use App\Models\Bug;
use App\Models\BugStatusHistory;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessGuestBugReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The guest bug report instance.
     */
    protected GuestBugReport $guestReport;

    /**
     * Create a new job instance.
     */
    public function __construct(GuestBugReport $guestReport)
    {
        $this->guestReport = $guestReport;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Approve the report and create the bug
            $bug = $this->guestReport->approve();

            // Notify Project Managers
            $this->notifyProjectManagers($bug);

            Log::info('Guest bug report processed successfully', [
                'guest_report_id' => $this->guestReport->id,
                'bug_id' => $bug->id,
                'ticket' => $this->guestReport->ticket,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process guest bug report', [
                'guest_report_id' => $this->guestReport->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Notify project managers about the new bug
     */
    protected function notifyProjectManagers(Bug $bug): void
    {
        $pmIds = User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Project Manager'))
            ->pluck('id');

        foreach ($pmIds as $pmId) {
            Notification::create([
                'user_id' => $pmId,
                'related_id' => $bug->id,
                'type' => 'BugReported',
                'message' => 'Bug dilaporkan: Bug #'.$bug->id.' - '.$bug->title,
                'is_read' => false,
                'created_at' => now(),
            ]);
        }
    }
}
