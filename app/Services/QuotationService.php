<?php

namespace App\Services;

use App\Enums\QuotationStatus;
use App\Models\Boq;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Owns the Quotation aggregate: the quotation itself, its line items, and
 * the append-only version snapshots taken whenever a quotation that has
 * already left Draft needs to change (spec §78 rule 1 — approved/sent
 * quotations are never casually edited). `reviseAfterSent()` is the one
 * path by which an in-flight quotation's content changes after that
 * point: snapshot first, then apply.
 */
class QuotationService
{
    public function __construct(protected NumberingService $numbering) {}

    public function create(Project $project, array $data, array $items = [], ?Boq $boq = null): Quotation
    {
        return DB::transaction(function () use ($project, $data, $items, $boq) {
            if ($boq && empty($items)) {
                $items = $boq->items->map(fn ($item) => [
                    'type' => 'item',
                    'description' => $item->item.($item->description ? " — {$item->description}" : ''),
                    'qty' => $item->qty,
                    'unit' => $item->unit,
                    'rate' => $item->rate,
                ])->all();
            }

            $quotation = $project->quotations()->create([
                'quotation_no' => $this->numbering->next('QUO'),
                'client_id' => $data['client_id'] ?? $project->client_id,
                'boq_id' => $boq?->id,
                'version' => 1,
                'status' => QuotationStatus::Draft->value,
                'discount' => $data['discount'] ?? 0,
                'tax' => $data['tax'] ?? 0,
                'valid_until' => $data['valid_until'] ?? null,
                'terms' => $data['terms'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncItems($quotation, $items);
            $this->recalculateTotals($quotation);

            return $quotation->fresh(['items']);
        });
    }

    /**
     * Updates a quotation's fields and item list. If the quotation has
     * already left Draft, the current state is snapshotted into
     * quotation_versions and the quotation is reset to Draft first — a
     * revision always starts a fresh review cycle rather than silently
     * mutating something the client already saw.
     */
    public function update(Quotation $quotation, array $data, array $items, ?User $actor = null): Quotation
    {
        return DB::transaction(function () use ($quotation, $data, $items, $actor) {
            if (! $quotation->status->isEditableDirectly()) {
                $this->snapshotAndReset($quotation, $actor);
            }

            $quotation->update([
                'discount' => $data['discount'] ?? $quotation->discount,
                'tax' => $data['tax'] ?? $quotation->tax,
                'valid_until' => $data['valid_until'] ?? $quotation->valid_until,
                'terms' => $data['terms'] ?? $quotation->terms,
                'notes' => $data['notes'] ?? $quotation->notes,
            ]);

            $this->syncItems($quotation, $items);
            $this->recalculateTotals($quotation);

            return $quotation->fresh(['items']);
        });
    }

    protected function snapshotAndReset(Quotation $quotation, ?User $actor): void
    {
        $quotation->versions()->create([
            'version' => $quotation->version,
            'snapshot' => [
                'status' => $quotation->status->value,
                'subtotal' => (string) $quotation->subtotal,
                'discount' => (string) $quotation->discount,
                'tax' => (string) $quotation->tax,
                'grand_total' => (string) $quotation->grand_total,
                'valid_until' => $quotation->valid_until?->toDateString(),
                'terms' => $quotation->terms,
                'notes' => $quotation->notes,
                'items' => $quotation->items->map(fn ($item) => [
                    'type' => $item->type->value,
                    'description' => $item->description,
                    'qty' => (string) $item->qty,
                    'unit' => $item->unit,
                    'rate' => (string) $item->rate,
                    'amount' => (string) $item->amount,
                ])->all(),
            ],
            'created_by' => $actor?->id,
        ]);

        $quotation->update([
            'version' => $quotation->version + 1,
            'status' => QuotationStatus::Draft->value,
        ]);
    }

    /**
     * Status transitions the quotation goes through on its way to being
     * accepted or not. Kept intentionally simple — any status can move
     * to any other here, since which transitions make sense is a UI
     * concern (the Show page only offers the sensible next steps); the
     * service's job is just to apply the change and stamp `valid_until`
     * defaults when sending for the first time.
     */
    public function changeStatus(Quotation $quotation, string $status): Quotation
    {
        $data = ['status' => $status];

        if ($status === QuotationStatus::Sent->value && ! $quotation->valid_until) {
            $data['valid_until'] = now()->addDays(30)->toDateString();
        }

        $quotation->update($data);

        return $quotation->fresh();
    }

    protected function syncItems(Quotation $quotation, array $items): void
    {
        $quotation->items()->delete();

        foreach ($items as $item) {
            $qty = (float) ($item['qty'] ?? 1);
            $rate = (float) ($item['rate'] ?? 0);

            $quotation->items()->create([
                'type' => $item['type'] ?? 'item',
                'description' => $item['description'],
                'qty' => $qty,
                'unit' => $item['unit'] ?? null,
                'rate' => $rate,
                'amount' => round($qty * $rate, 2),
            ]);
        }
    }

    public function recalculateTotals(Quotation $quotation): Quotation
    {
        $subtotal = (float) $quotation->items()->sum('amount');
        $grandTotal = $subtotal - (float) $quotation->discount + (float) $quotation->tax;

        $quotation->update([
            'subtotal' => $subtotal,
            'grand_total' => max($grandTotal, 0),
        ]);

        return $quotation->fresh();
    }
}
