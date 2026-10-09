<?php

namespace App\Services;

use App\Enums\BoqStatus;
use App\Models\Boq;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

/**
 * Owns the Boq aggregate (the BOQ itself plus its line items). A project
 * can accumulate several BOQ versions over time — re-estimates happen,
 * scope changes — so creating a new one never overwrites an old one; it
 * simply supersedes whichever BOQ was previously Active
 * (ARCHITECTURE.md §3.6).
 */
class BoqService
{
    public function __construct(protected NumberingService $numbering) {}

    public function create(Project $project, array $data, array $items = []): Boq
    {
        return DB::transaction(function () use ($project, $data, $items) {
            $version = (int) ($project->boqs()->max('version') ?? 0) + 1;

            $boq = $project->boqs()->create([
                'boq_no' => $this->numbering->next('BOQ'),
                'version' => $version,
                'status' => $data['status'] ?? BoqStatus::Draft->value,
                'discount' => $data['discount'] ?? 0,
                'tax' => $data['tax'] ?? 0,
            ]);

            $this->syncItems($boq, $items);
            $this->recalculateTotals($boq);

            if ($boq->status === BoqStatus::Active) {
                $this->activate($boq);
            }

            return $boq->fresh(['items']);
        });
    }

    public function update(Boq $boq, array $data, array $items): Boq
    {
        return DB::transaction(function () use ($boq, $data, $items) {
            $boq->update([
                'discount' => $data['discount'] ?? $boq->discount,
                'tax' => $data['tax'] ?? $boq->tax,
            ]);

            $this->syncItems($boq, $items);
            $this->recalculateTotals($boq);

            return $boq->fresh(['items']);
        });
    }

    /**
     * Marks this BOQ Active and flips every other BOQ on the same
     * project that was Active to Superseded — exactly one Active BOQ per
     * project at a time.
     */
    public function activate(Boq $boq): Boq
    {
        return DB::transaction(function () use ($boq) {
            Boq::where('project_id', $boq->project_id)
                ->where('id', '!=', $boq->id)
                ->where('status', BoqStatus::Active->value)
                ->update(['status' => BoqStatus::Superseded->value]);

            $boq->update(['status' => BoqStatus::Active->value]);

            return $boq->fresh();
        });
    }

    protected function syncItems(Boq $boq, array $items): void
    {
        $boq->items()->delete();

        foreach ($items as $item) {
            $qty = (float) ($item['qty'] ?? 1);
            $rate = (float) ($item['rate'] ?? 0);

            $boq->items()->create([
                'item' => $item['item'],
                'description' => $item['description'] ?? null,
                'category' => $item['category'] ?? null,
                'qty' => $qty,
                'unit' => $item['unit'] ?? null,
                'rate' => $rate,
                'amount' => round($qty * $rate, 2),
                'labour_cost' => $item['labour_cost'] ?? 0,
                'material_cost' => $item['material_cost'] ?? 0,
                'remarks' => $item['remarks'] ?? null,
            ]);
        }
    }

    public function recalculateTotals(Boq $boq): Boq
    {
        $subtotal = (float) $boq->items()->sum('amount');
        $grandTotal = $subtotal - (float) $boq->discount + (float) $boq->tax;

        $boq->update([
            'subtotal' => $subtotal,
            'grand_total' => max($grandTotal, 0),
        ]);

        return $boq->fresh();
    }
}
