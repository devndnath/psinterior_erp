<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The running per-(warehouse, product) stock balance. This model must
 * only ever be written through App\Services\InventoryService — never
 * directly from a Livewire component or controller — so that this
 * balance and the `stock_movements` ledger never drift apart (see the
 * comment on the `warehouse_stocks` migration).
 */
class WarehouseStock extends Model
{
    protected $fillable = ['warehouse_id', 'product_id', 'quantity'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:2'];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
