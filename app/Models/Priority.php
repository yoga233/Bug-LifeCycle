<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Priority extends Model
{
    protected $fillable = [
        'level',
        'sla_hours',
        'bg_color',
        'text_color',
    ];

    public function bugs(): HasMany
    {
        return $this->hasMany(Bug::class);
    }
}
