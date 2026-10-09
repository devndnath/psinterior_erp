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
        'design_id', 'version', 'file_document_id', 'uploaded_by',
        'description', 'client_feedback', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => DesignRevisionStatus::class,
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
}
