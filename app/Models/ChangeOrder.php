<?php

namespace App\Models;

use App\Enums\ApprovalDecision;
use App\Enums\ChangeOrderStatus;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChangeOrder extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'co_no', 'project_id', 'description', 'cost',
        'client_approval_status', 'approved_at', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ChangeOrderStatus::class,
            'client_approval_status' => ApprovalDecision::class,
            'cost' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function logAttributes(): array
    {
        return ['status', 'client_approval_status', 'cost'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ChangeOrderItem::class);
    }
}
