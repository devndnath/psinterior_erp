<?php

namespace App\Models\Concerns;

use App\Models\Client;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Reference — who introduced this lead/client to the firm. One of:
 *   'client'   an existing client      (reference_client_id)
 *   'employee' one of the staff        (reference_employee_id)
 *   'other'    anyone else             (reference_name, reference_phone)
 * Used by Lead and Client; a lead's reference is copied onto the client
 * it converts into (LeadService::convertToClient()).
 */
trait HasReference
{
    public const REFERENCE_FIELDS = [
        'reference_type', 'reference_client_id', 'reference_employee_id', 'reference_name', 'reference_phone',
    ];

    public function initializeHasReference(): void
    {
        $this->mergeFillable(self::REFERENCE_FIELDS);
    }

    public function referenceClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'reference_client_id');
    }

    public function referenceEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reference_employee_id');
    }

    public function hasReference(): bool
    {
        return (bool) $this->reference_type;
    }

    /** The referrer's display name (null when no reference is recorded). */
    public function referrerName(): ?string
    {
        return match ($this->reference_type) {
            'client' => $this->referenceClient?->name,
            'employee' => $this->referenceEmployee?->name,
            'other' => $this->reference_name,
            default => null,
        };
    }

    /** Human label for the kind of referrer. */
    public function referrerKind(): ?string
    {
        return match ($this->reference_type) {
            'client' => 'Existing client',
            'employee' => 'Staff',
            'other' => 'Other person',
            default => null,
        };
    }

    /** Just the reference columns, for copying onto another record. */
    public function referenceAttributes(): array
    {
        return $this->only(self::REFERENCE_FIELDS);
    }
}
