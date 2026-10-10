<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warranty extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'start_date', 'end_date', 'terms'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function isActive(): bool
    {
        $now = now();

        return $this->start_date !== null && $this->end_date !== null
            && $now->greaterThanOrEqualTo($this->start_date)
            && $now->lessThanOrEqualTo($this->end_date);
    }
}
