<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BugStatusHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'bug_id',
        'user_id',
        'old_status',
        'new_status',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
        ];
    }

    public function bug(): BelongsTo
    {
        return $this->belongsTo(Bug::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
