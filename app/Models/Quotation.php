<?php

namespace App\Models;

use App\Enums\QuotationStatus;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Once a quotation leaves Draft, it is never edited in place again
 * (spec §78 rule 1) — QuotationService::reviseAfterSent() snapshots the
 * current items/totals/status into `quotation_versions`, bumps
 * `version`, and resets status to Draft before applying the new changes.
 */
class Quotation extends Model
{
    use HasDocuments, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'quotation_no', 'client_id', 'project_id', 'boq_id', 'version',
        'status', 'subtotal', 'discount', 'tax', 'grand_total',
        'valid_until', 'terms', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => QuotationStatus::class,
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'valid_until' => 'date',
        ];
    }

    public function logAttributes(): array
    {
        return ['status', 'version', 'grand_total', 'valid_until'];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function boq(): BelongsTo
    {
        return $this->belongsTo(Boq::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(QuotationVersion::class)->orderByDesc('version');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}
