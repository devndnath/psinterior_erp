<?php

namespace App\Models;

use App\Enums\ApprovalDecision;
use App\Enums\DesignCategory;
use App\Enums\DesignStatus;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Design extends Model
{
    use HasDocuments, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'design_no', 'project_id', 'title', 'category', 'description',
        'uploaded_by', 'status', 'client_approval_status',
    ];

    protected function casts(): array
    {
        return [
            'category' => DesignCategory::class,
            'status' => DesignStatus::class,
            'client_approval_status' => ApprovalDecision::class,
        ];
    }

    public function logAttributes(): array
    {
        return ['title', 'category', 'status', 'client_approval_status'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(DesignRevision::class)->orderByDesc('version');
    }

    public function latestRevision(): ?DesignRevision
    {
        return $this->revisions->first();
    }
}
