<?php

namespace App\Models;

use App\Enums\StockTransferStatus;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'transfer_no', 'from_warehouse_id', 'to_warehouse_id', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return ['status' => StockTransferStatus::class];
    }

    public function logAttributes(): array
    {
        return ['status', 'from_warehouse_id', 'to_warehouse_id'];
    }

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }
}
