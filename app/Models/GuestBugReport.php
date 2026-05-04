<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestBugReport extends Model
{
    protected $fillable = [
        'ticket',
        'guest_name',
        'guest_email',
        'guest_version',
        'project_id',
        'severity_id',
        'title',
        'description',
        'reproduction_steps',
        'frequency',
        'queue_status',
        'pm_notes',
        'ip_address',
        'user_agent',
        'reported_at',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Get the project associated with this report
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the severity associated with this report
     */
    public function severity(): BelongsTo
    {
        return $this->belongsTo(Severity::class);
    }

    /**
     * Check if the report is pending approval
     */
    public function isPending(): bool
    {
        return $this->queue_status === 'pending';
    }

    /**
     * Check if the report has been approved
     */
    public function isApproved(): bool
    {
        return $this->queue_status === 'approved';
    }

    /**
     * Check if the report has been rejected
     */
    public function isRejected(): bool
    {
        return $this->queue_status === 'rejected';
    }

    /**
     * Approve the report and create a Bug from it
     */
    public function approve(): Bug
    {
        $this->update([
            'queue_status' => 'approved',
            'processed_at' => now(),
        ]);

        // Create the actual bug
        $bug = Bug::create([
            'project_id' => $this->project_id,
            'severity_id' => $this->severity_id,
            'priority_id' => null,
            'guest_name' => $this->guest_name,
            'guest_email' => $this->guest_email,
            'guest_version' => $this->guest_version,
            'title' => $this->title,
            'description' => $this->description,
            'frequency' => $this->frequency,
            'status' => 'Reported',
        ]);

        // Create status history
        BugStatusHistory::create([
            'bug_id' => $bug->id,
            'user_id' => null,
            'old_status' => 'Reported',
            'new_status' => 'Reported',
            'changed_at' => now(),
        ]);

        // Move attachments if any
        // (This would need additional logic if attachments are stored)

        return $bug;
    }

    /**
     * Reject the report
     */
    public function reject(string $notes = ''): void
    {
        $this->update([
            'queue_status' => 'rejected',
            'pm_notes' => $notes,
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark the report as expired
     */
    public function expire(): void
    {
        $this->update([
            'queue_status' => 'expired',
            'processed_at' => now(),
        ]);
    }

    /**
     * Generate a ticket for this report
     */
    public static function generateTicket(): string
    {
        return 'GBR-' . strtoupper(substr(md5(uniqid(microtime())), 0, 8));
    }
}
