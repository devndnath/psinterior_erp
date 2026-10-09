<?php

namespace App\Models;

use App\Models\Concerns\HasCompanyScope;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SiteVisit extends Model
{
    use HasCompanyScope, HasDocuments, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'company_id', 'sv_no', 'client_id', 'project_id', 'site_address',
        'visit_date', 'assigned_to', 'purpose', 'requirements', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
        ];
    }

    public function logAttributes(): array
    {
        return ['client_id', 'visit_date', 'assigned_to', 'purpose'];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SiteVisitItem::class);
    }

    public function totalArea(): float
    {
        return (float) $this->items->sum('area');
    }
}
