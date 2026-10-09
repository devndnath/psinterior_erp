<?php

namespace App\Models;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Enums\ProjectType;
use App\Models\Concerns\HasCompanyScope;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasCompanyScope, HasDocuments, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'company_id', 'lead_no', 'name', 'phone', 'email', 'company_name',
        'address', 'project_type', 'estimated_budget', 'interested_service',
        'lead_source_id', 'assigned_to', 'priority', 'status',
        'expected_closing_date', 'notes', 'converted_client_id', 'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'estimated_budget' => 'decimal:2',
            'expected_closing_date' => 'date',
            'converted_at' => 'datetime',
            'priority' => LeadPriority::class,
            'status' => LeadStatus::class,
            'project_type' => ProjectType::class,
        ];
    }

    public function logAttributes(): array
    {
        return ['name', 'phone', 'email', 'status', 'priority', 'assigned_to', 'estimated_budget'];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function followups(): HasMany
    {
        return $this->hasMany(LeadFollowup::class)->orderByDesc('date');
    }

    public function convertedClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'converted_client_id');
    }

    public function isConverted(): bool
    {
        return ! is_null($this->converted_client_id);
    }

    public function nextFollowup(): ?LeadFollowup
    {
        return $this->followups()
            ->where('status', \App\Enums\FollowupStatus::Pending)
            ->orderBy('date')
            ->first();
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', [LeadStatus::Won, LeadStatus::Lost]);
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }
}
