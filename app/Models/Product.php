<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'sku', 'name', 'category_id', 'brand', 'unit_id',
        'purchase_price', 'usage_rate', 'min_stock', 'description', 'image',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'usage_rate' => 'decimal:2',
            'min_stock' => 'decimal:2',
        ];
    }

    public function logAttributes(): array
    {
        return ['min_stock', 'purchase_price'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function warehouseStocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    /**
     * Total quantity of this product across every warehouse. Reads
     * straight from warehouse_stocks (the running balance
     * InventoryService keeps in sync), never from the movements ledger.
     */
    public function totalStock(): float
    {
        return (float) $this->warehouseStocks()->sum('quantity');
    }

    /**
     * True once total stock across all warehouses drops below the
     * reorder threshold set on this product.
     */
    public function isLowStock(): bool
    {
        return $this->totalStock() < (float) $this->min_stock;
    }
}
