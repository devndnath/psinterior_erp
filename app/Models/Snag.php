<?php

namespace App\Models;

use App\Enums\SnagStatus;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Snag extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'snag_no', 'project_id', 'inspection_id', 'area', 'issue',
        'assigned_to', 'deadline', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => SnagStatus::class,
            'deadline' => 'date',
        ];
    }

    public function logAttributes(): array
    {
        return ['status', 'assigned_to'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    /**
     * Named assignedTo(), not assigned(), to avoid ambiguity with
     * Eloquent's reserved words.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
