<?php

namespace App\Services;

use App\Enums\PurchaseRequestStatus;
use App\Enums\VendorQuotationStatus;
use App\Models\PurchaseRequest;
use App\Models\VendorQuotation;
use Illuminate\Support\Facades\DB;

/**
 * Owns the Vendor Quotation aggregate — one or more vendor quotes
 * recorded against a Purchase Request, one of which is eventually
 * selected to become a Purchase Order.
 */
class VendorQuotationService
{
    /**
     * The PR must have at least left Draft (it needs to have actually
     * been sent for quotation) before a quote can be recorded against
     * it — enforced here, not just at the Livewire layer, the same way
     * PurchaseRequestService::update() re-checks its own status.
     */
    public function create(PurchaseRequest $pr, array $data, array $items): VendorQuotation
    {
        if ($pr->status === PurchaseRequestStatus::Draft || $pr->status === PurchaseRequestStatus::Cancelled) {
            throw new \RuntimeException('The purchase request must be sent for quotation before a vendor quotation can be recorded.');
        }

        return DB::transaction(function () use ($pr, $data, $items) {
            $vq = $pr->vendorQuotations()->create([
                'vendor_id' => $data['vendor_id'],
                'status' => VendorQuotationStatus::Received->value,
                'total_amount' => 0,
            ]);

            $this->syncItems($vq, $items);
            $this->recalculateTotal($vq);

            return $vq->fresh(['items']);
        });
    }

    /**
     * Selects this quotation as the winner: rejects every other
     * quotation on the same purchase request and flips the request to
     * VendorSelected, all inside one transaction — mirrors
     * BoqService::activate()'s "flip siblings" pattern from Phase 5.
     */
    public function selectWinner(VendorQuotation $vq): VendorQuotation
    {
        return DB::transaction(function () use ($vq) {
            VendorQuotation::where('purchase_request_id', $vq->purchase_request_id)
                ->where('id', '!=', $vq->id)
                ->update(['status' => VendorQuotationStatus::Rejected->value]);

            $vq->update(['status' => VendorQuotationStatus::Selected->value]);

            $vq->purchaseRequest->update(['status' => PurchaseRequestStatus::VendorSelected->value]);

            return $vq->fresh();
        });
    }

    protected function syncItems(VendorQuotation $vq, array $items): void
    {
        $vq->items()->delete();

        foreach ($items as $item) {
            $qty = (float) ($item['qty'] ?? 0);
            $rate = (float) ($item['rate'] ?? 0);

            $vq->items()->create([
                'product_id' => $item['product_id'],
                'qty' => $qty,
                'rate' => $rate,
                'amount' => round($qty * $rate, 2),
            ]);
        }
    }

    protected function recalculateTotal(VendorQuotation $vq): void
    {
        $vq->update(['total_amount' => (float) $vq->items()->sum('amount')]);
    }
}
