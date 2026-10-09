<?php

namespace App\Models\Concerns;

use App\Models\Document;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Attach to any model that can own uploaded files (clients, projects,
 * designs, quotations, contracts, invoices, purchase orders, employees,
 * vendors, site visits — ARCHITECTURE.md §3.1 / spec §15). Actual uploads
 * always go through App\Services\FileService, never straight to the
 * `documents` table, so MIME/size validation and unique naming are never
 * bypassed.
 */
trait HasDocuments
{
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
