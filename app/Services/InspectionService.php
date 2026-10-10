<?php

namespace App\Services;

use App\Enums\InspectionResult;
use App\Models\Inspection;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

/**
 * Owns the Inspection aggregate and its checklist items. The overall
 * `result` is computed as the worst of its items (Failed >
 * RequiresCorrection > Passed) when items are given and the caller did
 * not explicitly pass a `result` in $data — an explicit result always
 * wins over the computed one.
 */
class InspectionService
{
    public function create(Project $project, array $data, array $items = []): Inspection
    {
        return DB::transaction(function () use ($project, $data, $items) {
            $inspection = $project->inspections()->create([
                'area' => $data['area'] ?? null,
                'inspection_date' => $data['inspection_date'],
                'inspector_id' => $data['inspector_id'] ?? null,
                'result' => $data['result'] ?? InspectionResult::Passed->value,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncItems($inspection, $items);

            if (! empty($items) && empty($data['result'])) {
                $this->recalculateResult($inspection);
            }

            return $inspection->fresh(['items']);
        });
    }

    public function update(Inspection $inspection, array $data, array $items = []): Inspection
    {
        return DB::transaction(function () use ($inspection, $data, $items) {
            $inspection->update([
                'area' => $data['area'] ?? $inspection->area,
                'inspection_date' => $data['inspection_date'] ?? $inspection->inspection_date,
                'inspector_id' => $data['inspector_id'] ?? $inspection->inspector_id,
                'notes' => $data['notes'] ?? $inspection->notes,
                'result' => $data['result'] ?? $inspection->result->value,
            ]);

            $this->syncItems($inspection, $items);

            if (! empty($items) && empty($data['result'])) {
                $this->recalculateResult($inspection);
            }

            return $inspection->fresh(['items']);
        });
    }

    protected function syncItems(Inspection $inspection, array $items): void
    {
        $inspection->items()->delete();

        foreach ($items as $item) {
            $inspection->items()->create([
                'checklist_item' => $item['checklist_item'],
                'result' => $item['result'] ?? InspectionResult::Passed->value,
                'notes' => $item['notes'] ?? null,
            ]);
        }
    }

    /**
     * Worst-of-items: any Failed item makes the overall result Failed;
     * else any RequiresCorrection makes it RequiresCorrection; else
     * Passed.
     */
    protected function recalculateResult(Inspection $inspection): void
    {
        $results = $inspection->items()->pluck('result');

        $overall = match (true) {
            $results->contains(InspectionResult::Failed->value) => InspectionResult::Failed,
            $results->contains(InspectionResult::RequiresCorrection->value) => InspectionResult::RequiresCorrection,
            default => InspectionResult::Passed,
        };

        $inspection->update(['result' => $overall->value]);
    }
}
