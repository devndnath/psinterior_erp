<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialRequisitionItem extends Model
{
    protected $fillable = ['material_requisition_id', 'product_id', 'quantity', 'issued_quantity'];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'issued_quantity' => 'decimal:2',
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
}
