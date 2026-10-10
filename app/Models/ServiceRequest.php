<?php

namespace App\Models;

use App\Enums\ServiceRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'sr_no', 'warranty_id', 'client_id', 'complaint', 'assigned_technician', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ServiceRequestStatus::class,
        ];
    }

    public function warranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Named assignedTechnician(), not assigned(), to avoid ambiguity
     * with Eloquent's reserved words.
     */
    public function assignedTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_technician');
    }
}
