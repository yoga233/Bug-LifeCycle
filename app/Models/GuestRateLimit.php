<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestRateLimit extends Model
{
    protected $fillable = [
        'ip_address',
        'email',
        'report_count',
        'first_report_at',
        'last_report_at',
        'is_blocked',
        'blocked_until',
        'block_reason',
    ];

    protected function casts(): array
    {
        return [
            'first_report_at' => 'datetime',
            'last_report_at' => 'datetime',
            'is_blocked' => 'boolean',
            'blocked_until' => 'datetime',
        ];
    }

    /**
     * Check if the guest is currently blocked
     */
    public function isBlocked(): bool
    {
        if (!$this->is_blocked) {
            return false;
        }

        // If blocked until is set and has passed, auto-unblock
        if ($this->blocked_until && $this->blocked_until->isPast()) {
            $this->update([
                'is_blocked' => false,
                'blocked_until' => null,
                'block_reason' => null,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Check if the guest can submit a report based on rate limits
     */
    public function canSubmitReport(int $maxPerHour = 5, int $maxPerDay = 20): bool
    {
        if ($this->isBlocked()) {
            return false;
        }

        $now = now();
        $oneHourAgo = $now->copy()->subHour();
        $oneDayAgo = $now->copy()->subDay();

        // Check hourly limit
        if ($this->last_report_at && $this->last_report_at->gte($oneHourAgo)) {
            if ($this->report_count >= $maxPerHour) {
                return false;
            }
        }

        // Check daily limit (if first report was today)
        if ($this->first_report_at && $this->first_report_at->gte($oneDayAgo)) {
            if ($this->report_count >= $maxPerDay) {
                return false;
            }
        }

        return true;
    }

    /**
     * Increment report count
     */
    public function incrementReportCount(): void
    {
        $this->increment('report_count');
        $this->update(['last_report_at' => now()]);
    }

    /**
     * Block the guest
     */
    public function block(string $reason, int $durationMinutes = 60): void
    {
        $this->update([
            'is_blocked' => true,
            'blocked_until' => now()->addMinutes($durationMinutes),
            'block_reason' => $reason,
        ]);
    }
}
