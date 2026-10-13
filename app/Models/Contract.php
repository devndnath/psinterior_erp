<?php

namespace App\Models;

use App\Enums\ContractStatus;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasDocuments, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'contract_no', 'client_id', 'project_id', 'quotation_id', 'amount',
        'start_date', 'end_date', 'payment_terms', 'warranty_terms',
        'terms', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContractStatus::class,
            'amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function logAttributes(): array
    {
        return ['status', 'amount', 'start_date', 'end_date'];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ContractItem::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function scheduleLines(): HasMany
    {
        return $this->hasMany(PaymentScheduleLine::class)->orderBy('sort_order')->orderBy('due_date');
    }
}
