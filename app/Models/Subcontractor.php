<?php

namespace App\Models;

use App\Enums\SubcontractorTrade;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subcontractor extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = ['name', 'trade', 'phone', 'address', 'rate_type'];

    protected function casts(): array
    {
        return [
            'trade' => SubcontractorTrade::class,
        ];
    }

    public function logAttributes(): array
    {
        return ['trade'];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(SubcontractorAssignment::class);
    }
}
