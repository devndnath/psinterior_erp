<?php

namespace App\Models;

use App\Enums\VendorQuotationStatus;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorQuotation extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['purchase_request_id', 'vendor_id', 'status', 'total_amount'];

    protected function casts(): array
    {
        return [
            'status' => VendorQuotationStatus::class,
            'total_amount' => 'decimal:2',
        ];
    }

    public function logAttributes(): array
    {
        return ['status', 'total_amount'];
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(VendorQuotationItem::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
