<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Immutable — no UpdatedAt timestamp, no soft deletes, and
 * ActivityLogPolicy denies delete/update to everyone except Super Admin
 * (spec §49, §78 rule 6).
 */
class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'causer_id', 'subject_id', 'subject_type', 'action',
        'description', 'old_values', 'new_values', 'ip_address', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
