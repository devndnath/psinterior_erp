<?php

namespace App\Services;

use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Enums\VendorQuotationStatus;
use App\Models\PurchaseOrder;
use App\Models\VendorQuotation;
use Illuminate\Support\Facades\DB;

/**
 * Owns the Purchase Order aggregate. A PO is either ad-hoc (create(), no
 * PR/VQ upstream) or raised from a selected Vendor Quotation
 * (createFromVendorQuotation()) — mirroring
 * ContractService::createFromQuotation()'s "create from an approved
 * upstream document, copying its items" pattern. Totals follow the same
 * header-level discount/tax arithmetic as Quotation/Invoice:
 * subtotal = Σ item amount, total = subtotal - discount + tax.
 */
class PurchaseOrderService
{
    public function __construct(protected NumberingService $numbering) {}

    public function create(array $data, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $items) {
            $po = PurchaseOrder::create([
                'po_no' => $this->numbering->next('PO'),
                'vendor_id' => $data['vendor_id'],
                'project_id' => $data['project_id'],
                'warehouse_id' => $data['warehouse_id'],
                'date' => $data['date'],
                'expected_delivery' => $data['expected_delivery'] ?? null,
                'discount' => $data['discount'] ?? 0,
                'tax' => $data['tax'] ?? 0,
                'terms' => $data['terms'] ?? null,
                'status' => PurchaseOrderStatus::Draft->value,
            ]);

            $this->syncItems($po, $items);
            $this->recalculateTotals($po);

            return $po->fresh(['items']);
        });
    }

    /**
     * Requires the quotation to be Selected — throws otherwise, the
     * same defensive re-check VendorQuotationService::selectWinner()'s
     * callers rely on never being bypassed by a direct service call.
     * Copies the VQ's items across, links purchase_request_id/
     * vendor_quotation_id/vendor_id, and flips the parent Purchase
     * Request to PoCreated, all in one transaction.
     */
    public function createFromVendorQuotation(VendorQuotation $vq, array $data): PurchaseOrder
    {
        if ($vq->status !== VendorQuotationStatus::Selected) {
            throw new \RuntimeException('Only a selected vendor quotation can be turned into a purchase order.');
        }

        return DB::transaction(function () use ($vq, $data) {
            $vq->loadMissing('items', 'purchaseRequest');

            $items = $vq->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'qty' => (float) $item->qty,
                'rate' => (float) $item->rate,
            ])->all();

            $po = PurchaseOrder::create([
                'po_no' => $this->numbering->next('PO'),
                'vendor_id' => $vq->vendor_id,
                'project_id' => $data['project_id'] ?? $vq->purchaseRequest->project_id,
                'warehouse_id' => $data['warehouse_id'],
                'purchase_request_id' => $vq->purchase_request_id,
                'vendor_quotation_id' => $vq->id,
                'date' => $data['date'] ?? now()->toDateString(),
                'expected_delivery' => $data['expected_delivery'] ?? null,
                'discount' => $data['discount'] ?? 0,
                'tax' => $data['tax'] ?? 0,
                'terms' => $data['terms'] ?? null,
                'status' => PurchaseOrderStatus::Draft->value,
            ]);

            $this->syncItems($po, $items);
            $this->recalculateTotals($po);

            $vq->purchaseRequest->update(['status' => PurchaseRequestStatus::PoCreated->value]);

            return $po->fresh(['items']);
        });
    }

    public function update(PurchaseOrder $po, array $data, array $items): PurchaseOrder
    {
        if ($po->status !== PurchaseOrderStatus::Draft) {
            throw new \RuntimeException('Only a draft purchase order can be edited.');
        }

        return DB::transaction(function () use ($po, $data, $items) {
            $po->update([
                'vendor_id' => $data['vendor_id'] ?? $po->vendor_id,
                'project_id' => $data['project_id'] ?? $po->project_id,
                'warehouse_id' => $data['warehouse_id'] ?? $po->warehouse_id,
                'date' => $data['date'] ?? $po->date,
                'expected_delivery' => $data['expected_delivery'] ?? $po->expected_delivery,
                'discount' => $data['discount'] ?? $po->discount,
                'tax' => $data['tax'] ?? $po->tax,
                'terms' => $data['terms'] ?? $po->terms,
            ]);

            $this->syncItems($po, $items);
            $this->recalculateTotals($po);

            return $po->fresh(['items']);
        });
    }

    public function send(PurchaseOrder $po): PurchaseOrder
    {
        if ($po->status !== PurchaseOrderStatus::Draft) {
            throw new \RuntimeException('Only a draft purchase order can be sent.');
        }

        $po->update(['status' => PurchaseOrderStatus::Sent->value]);

        return $po->fresh();
    }

    /**
     * Only while Draft or Sent — once any receiving has started
     * (PartiallyReceived/Received), stock has already moved against
     * this PO and it is never cancelled.
     */
    public function cancel(PurchaseOrder $po): PurchaseOrder
    {
        if (! in_array($po->status, [PurchaseOrderStatus::Draft, PurchaseOrderStatus::Sent], true)) {
            throw new \RuntimeException('A purchase order can only be cancelled while it is draft or sent.');
        }

        $po->update(['status' => PurchaseOrderStatus::Cancelled->value]);

        return $po->fresh();
    }

    /**
     * Called by GoodsReceiveService::create() after every GRN. Sums,
     * per PO item, the accepted quantity received across every GRN
     * (PurchaseOrderItem::totalAcceptedQty() — a plain per-row sum(),
     * never a HAVING on a non-aggregated column, the exact mistake that
     * broke the Phase 7 low-stock query under MySQL's default
     * ONLY_FULL_GROUP_BY mode). Received once every item's total meets
     * or exceeds its ordered qty; PartiallyReceived once any item has
     * some accepted qty but not all items are fully received; otherwise
     * left unchanged (e.g. a cancelled PO stays Cancelled).
     */
    public function recalculateReceiptStatus(PurchaseOrder $po): PurchaseOrder
    {
        $po->loadMissing('items');

        if (! in_array($po->status, [
            PurchaseOrderStatus::Sent,
            PurchaseOrderStatus::PartiallyReceived,
            PurchaseOrderStatus::Received,
        ], true)) {
            return $po;
        }

        $allFullyReceived = true;
        $anyReceived = false;

        foreach ($po->items as $item) {
            $accepted = $item->totalAcceptedQty();

            if ($accepted > 0) {
                $anyReceived = true;
            }

            if ($accepted < (float) $item->qty) {
                $allFullyReceived = false;
            }
        }

        if ($allFullyReceived) {
            $po->update(['status' => PurchaseOrderStatus::Received->value]);
        } elseif ($anyReceived) {
            $po->update(['status' => PurchaseOrderStatus::PartiallyReceived->value]);
        }

        return $po->fresh();
    }

    protected function syncItems(PurchaseOrder $po, array $items): void
    {
        $po->items()->delete();

        foreach ($items as $item) {
            $qty = (float) ($item['qty'] ?? 0);
            $rate = (float) ($item['rate'] ?? 0);
            $discount = (float) ($item['discount'] ?? 0);
            $tax = (float) ($item['tax'] ?? 0);

            $po->items()->create([
                'product_id' => $item['product_id'],
                'qty' => $qty,
                'rate' => $rate,
                'discount' => $discount,
                'tax' => $tax,
                'amount' => round(($qty * $rate) - $discount + $tax, 2),
            ]);
        }
    }

    protected function recalculateTotals(PurchaseOrder $po): void
    {
        $subtotal = (float) $po->items()->sum('amount');
        $total = $subtotal - (float) $po->discount + (float) $po->tax;

        $po->update([
            'subtotal' => $subtotal,
            'total' => max($total, 0),
        ]);
    }
}
