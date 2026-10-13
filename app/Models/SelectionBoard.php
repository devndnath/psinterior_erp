<?php

namespace App\Models;

use App\Enums\SelectionBoardStatus;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Closes the audit's "single biggest domain-specific gap": a structured
 * record of "client chose Option B tile for the master bath" instead of
 * ad-hoc notes or emails. HasDocuments here (not on SelectionOption) is
 * deliberate — options reference one image each via `file_document_id`
 * (DesignRevision's pattern), and FileService::store() needs *some*
 * model with the `documents` morphMany to attach through, same as
 * DesignService passes the parent Design rather than the revision.
 */
class SelectionBoard extends Model
{
    use HasDocuments, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'board_no', 'project_id', 'area', 'title', 'description', 'status',
        'decided_option_id', 'decided_at', 'client_comment', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => SelectionBoardStatus::class,
            'decided_at' => 'datetime',
        ];
    }

    public function logAttributes(): array
    {
        return ['title', 'area', 'status', 'decided_option_id'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function options(): HasMany
    {
        return $this->hasMany(SelectionOption::class);
    }

    public function decidedOption(): BelongsTo
    {
        return $this->belongsTo(SelectionOption::class, 'decided_option_id');
    }

    public function isLocked(): bool
    {
        return in_array($this->status, [SelectionBoardStatus::Decided, SelectionBoardStatus::Cancelled], true);
    }
}
