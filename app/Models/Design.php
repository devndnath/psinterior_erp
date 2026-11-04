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
        'design_no', 'project_id', 'client_id', 'title', 'category', 'price', 'description',
        'uploaded_by', 'status', 'client_approval_status',
    ];

    protected function casts(): array
    {
        return [
            'category' => DesignCategory::class,
            'price' => 'decimal:2',
            'status' => DesignStatus::class,
            'client_approval_status' => ApprovalDecision::class,
        ];
    }

    public function logAttributes(): array
    {
        return ['title', 'category', 'price', 'status', 'client_approval_status'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** Set only on a sold design (no project); a project design's client is the project's. */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** Design Sale invoices raised for this design. */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /** A design sold on its own, to a client, with no project. */
    public function isSold(): bool
    {
        return $this->project_id === null;
    }

    /** Whoever the design is for: its own client when sold, else the project's. */
    public function customerId(): ?int
    {
        return $this->client_id ?? $this->project?->client_id;
    }

    /** Designs a client can see: their projects' designs plus designs sold to them directly. */
    public function scopeForClient($query, int $clientId)
    {
        return $query->where(fn ($q) => $q
            ->where('designs.client_id', $clientId)
            ->orWhereIn('designs.project_id', Project::where('client_id', $clientId)->select('id')));
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
