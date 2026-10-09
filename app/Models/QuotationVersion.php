<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An immutable snapshot of a quotation's items/totals/status, taken the
 * moment it's revised after leaving Draft. Never updated after creation —
 * append-only, like design_revisions (ARCHITECTURE.md §3.6 / §13).
 */
class QuotationVersion extends Model
{
    use HasFactory;

    protected $fillable = ['quotation_id', 'version', 'snapshot', 'created_by'];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
