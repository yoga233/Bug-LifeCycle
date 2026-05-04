<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'bug_id',
        'user_id',
        'status',
        'response_code',
        'response_body',
        'attempted_at',
    ];

    protected function casts(): array
    {
        return [
            'attempted_at' => 'datetime',
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
