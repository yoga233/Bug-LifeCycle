<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Bug extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'severity_id',
        'priority_id',
        'assignee_id',
        'guest_name',
        'guest_email',
        'guest_version',
        'title',
        'description',
        'frequency',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function severity(): BelongsTo
    {
        return $this->belongsTo(Severity::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(BugStatusHistory::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'related_id');
    }

    public function integrationLogs(): HasMany
    {
        return $this->hasMany(IntegrationLog::class);
    }
}
