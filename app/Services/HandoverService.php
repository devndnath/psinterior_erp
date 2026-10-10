<?php

namespace App\Services;

use App\Models\Handover;
use App\Models\Project;

/**
 * Owns the Handover aggregate. create() throws if a handover already
 * exists for this project — the unique project_id constraint enforces
 * it at the DB level too, but this gives a clean application-level
 * error. confirmByClient() is idempotent by design (re-confirming just
 * updates the timestamp rather than throwing) — kept simple rather than
 * guarding against a double-click.
 */
class HandoverService
{
    public function create(Project $project, array $data): Handover
    {
        if ($project->handover()->exists()) {
            throw new \RuntimeException('This project already has a handover recorded.');
        }

        return $project->handover()->create([
            'handover_date' => $data['handover_date'],
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * No client portal yet, so this is staff-recorded on the client's
     * behalf. Idempotent: calling it again simply updates the timestamp.
     */
    public function confirmByClient(Handover $handover): Handover
    {
        $handover->update(['client_confirmed_at' => now()]);

        return $handover->fresh();
    }
}
