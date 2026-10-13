<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialRequisitionItem extends Model
{
    protected $fillable = ['material_requisition_id', 'product_id', 'quantity', 'issued_quantity', 'returned_quantity'];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'issued_quantity' => 'decimal:2',
            'returned_quantity' => 'decimal:2',
        ];
    }

    public function materialRequisition(): BelongsTo
    {
        return $this->belongsTo(MaterialRequisition::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * What actually stayed on site / got used, as opposed to what was
     * handed out at the counter (issued_quantity) — the figure the
     * Section-C site-operations gap ("material consumption/wastage/
     * return") was missing. No separate "consumed" column exists because
     * it's always derivable: issued minus whatever came back.
     */
    public function consumedQuantity(): float
    {
        return (float) $this->issued_quantity - (float) $this->returned_quantity;
    }

    public function returnableQuantity(): float
    {
        return max(0.0, (float) $this->issued_quantity - (float) $this->returned_quantity);
    }
}
