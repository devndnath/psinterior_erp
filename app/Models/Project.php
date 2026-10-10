<?php

namespace App\Models;

use App\Enums\LeadPriority;
use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use App\Models\Concerns\HasCompanyScope;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasCompanyScope, HasDocuments, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'company_id', 'project_code', 'name', 'client_id', 'lead_id', 'type',
        'location', 'start_date', 'expected_end_date', 'actual_end_date',
        'contract_value', 'estimated_budget', 'project_manager_id',
        'designer_id', 'site_engineer_id', 'supervisor_id', 'priority',
        'status', 'progress', 'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProjectType::class,
            'priority' => LeadPriority::class,
            'status' => ProjectStatus::class,
            'start_date' => 'date',
            'expected_end_date' => 'date',
            'actual_end_date' => 'date',
        ];
    }

    public function logAttributes(): array
    {
        return ['name', 'status', 'priority', 'project_manager_id', 'contract_value'];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function designer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'designer_id');
    }

    public function siteEngineer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'site_engineer_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Ad-hoc team roster beyond the four named lead roles above
     * (project_manager/designer/site_engineer/supervisor) — anyone else
     * who needs visibility into this project (ARCHITECTURE.md §2).
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role_on_project')
            ->withTimestamps();
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function siteVisits(): HasMany
    {
        return $this->hasMany(SiteVisit::class);
    }

    public function convertedFromLead(): HasMany
    {
        // Inverse of leads.converted_project_id — normally at most one row.
        return $this->hasMany(Lead::class, 'converted_project_id');
    }

    public function designs(): HasMany
    {
        return $this->hasMany(Design::class);
    }

    public function boqs(): HasMany
    {
        return $this->hasMany(Boq::class);
    }

    public function activeBoq(): ?Boq
    {
        return $this->boqs()->where('status', \App\Enums\BoqStatus::Active->value)->first();
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * A project typically has one active contract at a time
     * (ARCHITECTURE.md §2: "projects 1-1 contracts"), though history is
     * kept — older contracts are simply Completed/Terminated, never
     * deleted.
     */
    public function activeContract(): ?Contract
    {
        return $this->contracts()->where('status', \App\Enums\ContractStatus::Active->value)->first();
    }

    public function changeOrders(): HasMany
    {
        return $this->hasMany(ChangeOrder::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function materialRequisitions(): HasMany
    {
        return $this->hasMany(MaterialRequisition::class);
    }

    // Meetings, Inspections, Snags, Handovers and Warranties relations are
    // added to this model as each of those modules ships
    // (ARCHITECTURE.md §2) — intentionally not stubbed here.
}
