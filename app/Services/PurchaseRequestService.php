<?php

namespace App\Services;

use App\Enums\PurchaseRequestStatus;
use App\Models\Project;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\DB;

/**
 * Owns the Purchase Request aggregate — the first step of the
 * procurement pipeline: Draft -> SentForQuotation -> VendorSelected ->
 * PoCreated, with Cancelled reachable any time before PoCreated.
 *
 * Permission gating for this whole module rides on the existing
 * `*_purchase_orders` abilities rather than a `purchase_requests`
 * permission set — see PurchaseRequestPolicy's docblock.
 */
class PurchaseRequestService
{
    public function __construct(protected NumberingService $numbering) {}

    public function create(Project $project, array $data, array $items): PurchaseRequest
    {
        return DB::transaction(function () use ($project, $data, $items) {
            $pr = $project->purchaseRequests()->create([
                'pr_no' => $this->numbering->next('PR'),
                'requested_by' => $data['requested_by'] ?? null,
                'status' => PurchaseRequestStatus::Draft->value,
            ]);

            $this->syncItems($pr, $items);

            return $pr->fresh(['items']);
        });
    }

    /**
     * Only meaningful while the request is still Draft — enforced by
     * PurchaseRequestPolicy::update() at the Livewire layer, and
     * re-checked here the same way InvoiceService::update() refuses a
     * Paid invoice, so a direct service call can never bypass it either.
     */
    public function update(PurchaseRequest $pr, array $data, array $items): PurchaseRequest
    {
        if ($pr->status !== PurchaseRequestStatus::Draft) {
            throw new \RuntimeException('Only a draft purchase request can be edited.');
        }

        return DB::transaction(function () use ($pr, $data, $items) {
            $pr->update([
                'requested_by' => $data['requested_by'] ?? $pr->requested_by,
            ]);

            $this->syncItems($pr, $items);

            return $pr->fresh(['items']);
        });
    }

    public function sendForQuotation(PurchaseRequest $pr): PurchaseRequest
    {
        if ($pr->status !== PurchaseRequestStatus::Draft) {
            throw new \RuntimeException('Only a draft purchase request can be sent for quotation.');
        }

        $pr->update(['status' => PurchaseRequestStatus::SentForQuotation->value]);

        return $pr->fresh();
    }

    /**
     * Allowed any time before PoCreated — once a PO exists off this
     * request, cancelling it would orphan that PO, so it's refused.
     */
    public function cancel(PurchaseRequest $pr): PurchaseRequest
    {
        if ($pr->status === PurchaseRequestStatus::PoCreated) {
            throw new \RuntimeException('A purchase request that already has a purchase order cannot be cancelled.');
        }

        $pr->update(['status' => PurchaseRequestStatus::Cancelled->value]);

        return $pr->fresh();
    }

    protected function syncItems(PurchaseRequest $pr, array $items): void
    {
        $pr->items()->delete();

        foreach ($items as $item) {
            $pr->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => (float) ($item['quantity'] ?? 0),
            ]);
        }
    }
}
