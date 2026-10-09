<?php

namespace App\Models;

use App\Enums\FollowupStatus;
use App\Enums\FollowupType;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadFollowup extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'lead_id', 'date', 'time', 'type', 'notes',
        'next_followup_date', 'assigned_to', 'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'next_followup_date' => 'date',
            'type' => FollowupType::class,
            'status' => FollowupStatus::class,
        ];
    }

    public function logAttributes(): array
    {
        return ['lead_id', 'date', 'type', 'status', 'next_followup_date'];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function isOverdue(): bool
    {
        return $this->status === FollowupStatus::Pending && $this->date->isPast();
    }
}
