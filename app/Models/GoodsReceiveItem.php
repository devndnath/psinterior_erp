<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiveItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_receive_id', 'purchase_order_item_id', 'ordered_qty',
        'received_qty', 'damaged_qty', 'accepted_qty',
    ];

    protected function casts(): array
    {
        return [
            'ordered_qty' => 'decimal:2',
            'received_qty' => 'decimal:2',
            'damaged_qty' => 'decimal:2',
            'accepted_qty' => 'decimal:2',
        ];
    }

    public function goodsReceive(): BelongsTo
    {
        return $this->belongsTo(GoodsReceive::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }
}
