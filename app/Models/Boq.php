<?php

namespace App\Models;

use App\Enums\BoqStatus;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A Bill of Quantities for a project. Versioned rather than edited away:
 * BoqService::createVersion() always inserts a new row with the next
 * version number and flips the project's previous Active BOQ to
 * Superseded, so estimating history is never lost (ARCHITECTURE.md §3.6).
 */
class Boq extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'boq_no', 'project_id', 'version', 'status',
        'subtotal', 'discount', 'tax', 'grand_total',
    ];

    protected function casts(): array
    {
        return [
            'status' => BoqStatus::class,
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    public function logAttributes(): array
    {
        return ['status', 'version', 'grand_total'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BoqItem::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }
}
