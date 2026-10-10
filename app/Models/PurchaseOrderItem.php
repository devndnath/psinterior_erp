<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['purchase_order_id', 'product_id', 'qty', 'rate', 'discount', 'tax', 'amount'];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'rate' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function goodsReceiveItems(): HasMany
    {
        return $this->hasMany(GoodsReceiveItem::class);
    }

    /**
     * Total accepted quantity received against this line so far, across
     * every GRN — the figure PurchaseOrderService::recalculateReceiptStatus()
     * compares against `qty` to decide the PO's status.
     */
    public function totalAcceptedQty(): float
    {
        return (float) $this->goodsReceiveItems()->sum('accepted_qty');
    }
}
