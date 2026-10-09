<?php

namespace App\Models;

use App\Enums\ClientType;
use App\Models\Concerns\HasCompanyScope;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasCompanyScope, HasDocuments, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'company_id', 'client_no', 'name', 'type', 'phone', 'email',
        'company_name', 'address', 'billing_address', 'site_address',
        'tax_id', 'assigned_to', 'user_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => ClientType::class,
        ];
    }

    public function logAttributes(): array
    {
        return ['name', 'phone', 'email', 'assigned_to', 'type'];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function portalUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(ClientContact::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'converted_client_id');
    }

    public function siteVisits(): HasMany
    {
        return $this->hasMany(SiteVisit::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function primaryContact(): ?ClientContact
    {
        return $this->contacts->firstWhere('is_primary', true) ?? $this->contacts->first();
    }

    // Invoices, payments, meetings and complaints relations are added to
    // this model as each module ships (ARCHITECTURE.md §6 — Client 360
    // view assembles all of them, spec §6). Intentionally not stubbed
    // here to avoid referencing models that don't exist yet.
}
