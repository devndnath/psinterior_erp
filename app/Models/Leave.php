<?php

namespace App\Models;

use App\Enums\LeaveApprovalStatus;
use App\Enums\LeaveType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'type', 'from_date', 'to_date', 'reason',
        'manager_status', 'hr_status',
    ];

    protected function casts(): array
    {
        return [
            'type' => LeaveType::class,
            'from_date' => 'date',
            'to_date' => 'date',
            'manager_status' => LeaveApprovalStatus::class,
            'hr_status' => LeaveApprovalStatus::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isFullyApproved(): bool
    {
        return $this->manager_status === LeaveApprovalStatus::Approved
            && $this->hr_status === LeaveApprovalStatus::Approved;
    }

    public function isRejected(): bool
    {
        return $this->manager_status === LeaveApprovalStatus::Rejected
            || $this->hr_status === LeaveApprovalStatus::Rejected;
    }
}
