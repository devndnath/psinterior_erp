<?php

namespace App\Models;

use App\Enums\ExpenseClaimStatus;
use App\Enums\ExpenseClaimType;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An employee's reimbursement claim — conveyance to a site visit/client
 * meeting, meals, accommodation, etc. — as opposed to App\Models\Expense,
 * which is company/project spend paid to a vendor. See the migration's
 * docblock for the full distinction.
 */
class ExpenseClaim extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'employee_id', 'project_id', 'site_visit_id', 'type', 'date',
        'amount', 'description', 'status', 'reviewed_by', 'reviewed_at',
        'payroll_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => ExpenseClaimType::class,
            'status' => ExpenseClaimStatus::class,
            'date' => 'date',
            'amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function logAttributes(): array
    {
        return ['amount', 'type', 'status'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function siteVisit(): BelongsTo
    {
        return $this->belongsTo(SiteVisit::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function isPending(): bool
    {
        return $this->status === ExpenseClaimStatus::Pending;
    }
}
