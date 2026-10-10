<?php

namespace App\Models;

use App\Enums\MaterialRequisitionStatus;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialRequisition extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'mr_no', 'project_id', 'requested_by', 'warehouse_id', 'date', 'purpose', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => MaterialRequisitionStatus::class,
            'date' => 'date',
        ];
    }

    public function logAttributes(): array
    {
        return ['status', 'warehouse_id'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MaterialRequisitionItem::class);
    }
}
