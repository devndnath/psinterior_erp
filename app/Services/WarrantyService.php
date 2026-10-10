<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Warranty;

class WarrantyService
{
    public function create(Project $project, array $data): Warranty
    {
        return $project->warranties()->create([
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'terms' => $data['terms'] ?? null,
        ]);
    }

    public function update(Warranty $warranty, array $data): Warranty
    {
        $warranty->update([
            'start_date' => $data['start_date'] ?? $warranty->start_date,
            'end_date' => $data['end_date'] ?? $warranty->end_date,
            'terms' => $data['terms'] ?? $warranty->terms,
        ]);

        return $warranty->fresh();
    }

    /** True if ANY of the project's warranties covers right now. */
    public function isCurrentlyUnderWarranty(Project $project): bool
    {
        return $project->warranties->contains(fn (Warranty $w) => $w->isActive());
    }
}
