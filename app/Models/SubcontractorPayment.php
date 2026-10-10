<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubcontractorPayment extends Model
{
    use HasFactory;

    protected $fillable = ['subcontractor_assignment_id', 'date', 'amount', 'notes'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(SubcontractorAssignment::class, 'subcontractor_assignment_id');
    }
}
