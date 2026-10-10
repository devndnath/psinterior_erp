<?php

namespace App\Services;

use App\Enums\SnagStatus;
use App\Models\Inspection;
use App\Models\Project;
use App\Models\Snag;

/**
 * Owns the Snag status chain: Pending -> Assigned -> InProgress -> Fixed
 * -> Verified -> Closed. reopen() is a direct escape hatch back to
 * Pending from any state except Closed, for the real-world case where a
 * "fixed" snag turns out not to be.
 */
class SnagService
{
    public function __construct(protected NumberingService $numbering) {}

    public function create(Project $project, array $data, ?Inspection $inspection = null): Snag
    {
        return $project->snags()->create([
            'snag_no' => $this->numbering->next('SNAG'),
            'inspection_id' => $inspection?->id ?? ($data['inspection_id'] ?? null),
            'area' => $data['area'] ?? null,
            'issue' => $data['issue'],
            'assigned_to' => $data['assigned_to'] ?? null,
            'deadline' => $data['deadline'] ?? null,
            'status' => SnagStatus::Pending->value,
        ]);
    }

    public function update(Snag $snag, array $data): Snag
    {
        $snag->update([
            'area' => $data['area'] ?? $snag->area,
            'issue' => $data['issue'] ?? $snag->issue,
            'deadline' => $data['deadline'] ?? $snag->deadline,
        ]);

        return $snag->fresh();
    }

    public function assign(Snag $snag, int $userId): Snag
    {
        $this->assertStatus($snag, SnagStatus::Pending);

        $snag->update(['assigned_to' => $userId, 'status' => SnagStatus::Assigned->value]);

        return $snag->fresh();
    }

    public function startProgress(Snag $snag): Snag
    {
        $this->assertStatus($snag, SnagStatus::Assigned);

        $snag->update(['status' => SnagStatus::InProgress->value]);

        return $snag->fresh();
    }

    public function markFixed(Snag $snag): Snag
    {
        $this->assertStatus($snag, SnagStatus::InProgress);

        $snag->update(['status' => SnagStatus::Fixed->value]);

        return $snag->fresh();
    }

    public function verify(Snag $snag): Snag
    {
        $this->assertStatus($snag, SnagStatus::Fixed);

        $snag->update(['status' => SnagStatus::Verified->value]);

        return $snag->fresh();
    }

    public function close(Snag $snag): Snag
    {
        $this->assertStatus($snag, SnagStatus::Verified);

        $snag->update(['status' => SnagStatus::Closed->value]);

        return $snag->fresh();
    }

    /**
     * Real-world escape hatch: a "fixed"/"verified" snag can turn out
     * not to be — reachable from any status except Closed.
     */
    public function reopen(Snag $snag): Snag
    {
        if ($snag->status === SnagStatus::Closed) {
            throw new \RuntimeException('A closed snag cannot be reopened.');
        }

        $snag->update(['status' => SnagStatus::Pending->value]);

        return $snag->fresh();
    }

    protected function assertStatus(Snag $snag, SnagStatus $expected): void
    {
        if ($snag->status !== $expected) {
            throw new \RuntimeException("This snag must be {$expected->label()} for this action.");
        }
    }
}
