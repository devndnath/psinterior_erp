<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubcontractorAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'subcontractor_id', 'project_id', 'work_description', 'rate', 'progress',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'progress' => 'integer',
        ];
    }

    public function subcontractor(): BelongsTo
    {
        return $this->belongsTo(Subcontractor::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubcontractorPayment::class);
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }
}
