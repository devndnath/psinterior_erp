<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoqItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'boq_id', 'item', 'description', 'category', 'qty', 'unit',
        'rate', 'amount', 'labour_cost', 'material_cost', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'rate' => 'decimal:2',
            'amount' => 'decimal:2',
            'labour_cost' => 'decimal:2',
            'material_cost' => 'decimal:2',
        ];
    }

    public function boq(): BelongsTo
    {
        return $this->belongsTo(Boq::class);
    }
}
