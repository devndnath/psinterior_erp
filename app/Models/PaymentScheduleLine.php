<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentScheduleLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id', 'milestone_id', 'invoice_id', 'label',
        'percentage', 'amount', 'due_date', 'sort_order', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
            'amount' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ProjectMilestone::class, 'milestone_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function isInvoiced(): bool
    {
        return $this->invoice_id !== null;
    }

    /**
     * Past its due date with no invoice raised against it yet. Once
     * linked to an invoice the line has done its job regardless of date
     * — whether that invoice gets paid late is the invoice's own
     * overdue tracking (InvoiceStatus::Overdue), not this line's.
     */
    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isPast()
            && ! $this->isInvoiced();
    }
}
