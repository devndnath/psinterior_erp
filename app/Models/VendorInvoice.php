<?php

namespace App\Models;

use App\Enums\VendorInvoiceStatus;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorInvoice extends Model
{
    use HasDocuments, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'vi_no', 'purchase_order_id', 'vendor_id', 'vendor_invoice_no',
        'invoice_date', 'amount', 'notes', 'status',
        'reviewed_by', 'reviewed_at', 'review_notes', 'expense_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => VendorInvoiceStatus::class,
            'invoice_date' => 'date',
            'amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function logAttributes(): array
    {
        return ['status', 'amount'];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function isReviewed(): bool
    {
        return $this->status !== VendorInvoiceStatus::Submitted;
    }
}
