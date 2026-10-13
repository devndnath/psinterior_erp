<?php

namespace App\Models;

use App\Enums\DesignRevisionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DesignRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'design_id', 'version', 'drawing_no', 'issue_date', 'file_document_id', 'uploaded_by',
        'description', 'client_feedback', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => DesignRevisionStatus::class,
            'issue_date' => 'date',
        ];
    }

    public function design(): BelongsTo
    {
        return $this->belongsTo(Design::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'file_document_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(DesignApproval::class)->latest('decided_at');
    }

    /**
     * True once a later version of the same design exists — meaning this
     * file is no longer the one to build from on site. Derived, not
     * stored, so it can never fall out of sync with the append-only
     * revision history the way a manually-set flag could.
     */
    public function isSuperseded(): bool
    {
        $maxVersion = $this->relationLoaded('design') && $this->design->relationLoaded('revisions')
            ? $this->design->revisions->max('version')
            : $this->design()->first()?->revisions()->max('version');

        return ($maxVersion ?? $this->version) > $this->version;
    }
}
