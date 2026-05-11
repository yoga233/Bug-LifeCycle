<?php

namespace App\Services;

use App\Models\Bug;
use App\Models\BugStatusHistory;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BugStatusService
{
    /**
     * Change bug status and always write history.
     *
     * @throws InvalidArgumentException
     */
    public function transition(Bug $bug, string $toStatus, ?User $actor = null, ?TicketService $tickets = null): Bug
    {
        $fromStatus = (string) $bug->status;
        $toStatus = $this->normalizeStatus($toStatus);

        if ($fromStatus === $toStatus) {
            return $bug;
        }

        $this->assertAllowedTransition($fromStatus, $toStatus);

        return DB::transaction(function () use ($bug, $fromStatus, $toStatus, $actor) {
            $bug->forceFill(['status' => $toStatus]);
            $bug->save();

            $qaIds = null;

            if ($toStatus === 'Testing') {
                $qaIds = User::query()
                    ->where('is_active', true)
                    ->whereHas('roles', fn ($q) => $q->where('name', 'QA'))
                    ->pluck('id');
            }

            BugStatusHistory::create([
                'bug_id' => $bug->id,
                'user_id' => $actor?->id,
                'old_status' => $fromStatus,
                'new_status' => $toStatus,
                'changed_at' => now(),
            ]);

            // Invalidate PM dashboard cache on status change (CRITICAL for data consistency)
            $this->invalidatePmCache('status_changed');

            // Notify assignee about status change (if any) - except for Testing status (handled separately)
            if ($bug->assignee_id && $toStatus !== 'Testing') {
                $ticketLabel = $tickets ? $tickets->fromBugId($bug->id) : $bug->id;
                Notification::create([
                    'user_id' => $bug->assignee_id,
                    'related_id' => $bug->id,
                    'type' => 'BugStatusChanged',
                    'message' => "Bug #{$ticketLabel} status: {$this->toStatusLabel($fromStatus)} → {$this->toStatusLabel($toStatus)}",
                    'is_read' => false,
                    'created_at' => now(),
                ]);
            }

            // Notify Project Manager when a bug is finished (Resolved/Closed)
            if (in_array($toStatus, ['Resolved', 'Closed'], true)) {
                $pmIds = User::query()
                    ->where('is_active', true)
                    ->whereHas('roles', fn ($q) => $q->where('name', 'Project Manager'))
                    ->pluck('id');

                foreach ($pmIds as $pmId) {
                    $ticketLabel = $tickets ? $tickets->fromBugId($bug->id) : $bug->id;
                    Notification::create([
                        'user_id' => $pmId,
                        'related_id' => $bug->id,
                        'type' => 'BugDone',
                        'message' => 'Bug #'.$ticketLabel.' selesai ('.$this->toStatusLabel($toStatus).'): '.$bug->title,
                        'is_read' => false,
                        'created_at' => now(),
                    ]);
                }
            }

            // Notify QA when bug enters testing stage.
            if ($toStatus === 'Testing') {
                // First notify the assignee (programmer) that bug is sent to testing
                if ($bug->assignee_id) {
                    $ticketLabel = $tickets ? $tickets->fromBugId($bug->id) : $bug->id;
                    Notification::create([
                        'user_id' => $bug->assignee_id,
                        'related_id' => $bug->id,
                        'type' => 'BugStatusChanged',
                        'message' => "Bug #{$ticketLabel} status: {$this->toStatusLabel($fromStatus)} → {$this->toStatusLabel($toStatus)}",
                        'is_read' => false,
                        'created_at' => now(),
                    ]);
                }

                // Then notify all QA users using bulk insert for efficiency
                $qaNotifications = [];
                $ticketLabel = $tickets ? $tickets->fromBugId($bug->id) : $bug->id;
                foreach ($qaIds ?? [] as $qaId) {
                    $qaNotifications[] = [
                        'user_id' => $qaId,
                        'related_id' => $bug->id,
                        'type' => 'BugStatusChanged',
                        'message' => 'Bug #'.$ticketLabel.' siap untuk pengujian: '.$bug->title,
                        'is_read' => false,
                        'created_at' => now(),
                    ];
                }

                if (!empty($qaNotifications)) {
                    Notification::insert($qaNotifications);
                }
            }

            return $bug->refresh();
        });
    }

    /**
     * Invalidate PM dashboard cache to ensure data consistency.
     * This prevents stale data after bug status changes.
     *
     * @param string $changeType Type of change that triggered invalidation
     * @return void
     */
    private function invalidatePmCache(string $changeType = 'updated'): void
    {
        try {
            // Use Cache::forget to clear specific keys
            // Time-based cache keys need to clear recent hours
            $now = now();
            
            // Clear current hour and last 2 hours
            for ($i = 0; $i <= 2; $i++) {
                $hourKey = $now->copy()->subHours($i)->format('Y-m-d-H');
                Cache::forget('pm:dashboard:stats:' . $hourKey);
            }

            // Clear programmers list cache
            Cache::forget('pm:dashboard:programmers');
            Cache::forget('pm:performance:programmers');
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            // Cache failure shouldn't block bug status change
            \Log::warning('Cache invalidation failed during bug status change', [
                'error' => $e->getMessage(),
                'change_type' => $changeType,
            ]);
        }
    }

    private function normalizeStatus(string $status): string
    {
        // Ensure it matches enum values in bugs.status migration
        $status = trim($status);

        $allowed = [
            'Reported',
            'Assigned',
            'In Progress',
            'Testing',
            'Resolved',
            'Closed',
            'Rejected',
        ];

        // allow case-insensitive input
        foreach ($allowed as $a) {
            if (strcasecmp($a, $status) === 0) {
                return $a;
            }
        }

        throw new InvalidArgumentException('Unknown status.');
    }

    private function toStatusLabel(string $status): string
    {
        return match (trim($status)) {
            'Reported' => 'Dilaporkan',
            'Assigned' => 'Ditugaskan',
            'In Progress' => 'Dalam Pengerjaan',
            'Testing' => 'Pengujian',
            'Resolved' => 'Diselesaikan',
            'Closed' => 'Ditutup',
            'Rejected' => 'Dikembalikan',
            default => $status,
        };
    }

    private function assertAllowedTransition(string $from, string $to): void
    {
        $map = [
            'Reported' => ['Assigned'],
            // Assigned -> Reported is reserved for PM unassign action (before work starts)
            'Assigned' => ['In Progress', 'Reported'],
            'In Progress' => ['Testing'],
            // QA can return failed testing result back to active dev work.
            'Testing' => ['Resolved', 'In Progress', 'Rejected'],
            'Resolved' => ['Closed'],
            'Rejected' => [],
            'Closed' => [],
        ];

        $allowed = $map[$from] ?? [];
        if (! in_array($to, $allowed, true)) {
            throw new InvalidArgumentException("Transition not allowed: {$from} -> {$to}");
        }
    }
}
