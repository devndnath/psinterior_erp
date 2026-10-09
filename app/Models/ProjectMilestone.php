<?php

namespace App\Models;

use App\Enums\LeadPriority;
use App\Enums\MilestoneStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'name', 'description', 'start_date', 'due_date',
        'assigned_to', 'status', 'progress', 'priority', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'status' => MilestoneStatus::class,
            'priority' => LeadPriority::class,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'milestone_id');
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isPast()
            && $this->status !== MilestoneStatus::Completed;
    }
}
