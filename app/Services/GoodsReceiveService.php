<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\GoodsReceive;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;

/**
 * Owns the Goods Receive (GRN) aggregate — the other major caller of
 * InventoryService::recordMovement() alongside
 * StockTransferService/MaterialRequisitionService (Phase 7's summary
 * already flagged this). A GRN is immutable once created (no update,
 * mirrors PaymentPolicy's "a recorded payment/receipt is never edited"
 * precedent) — a correction is a separate, later GRN, never an edit.
 */
class GoodsReceiveService
{
    public function __construct(
        protected NumberingService $numbering,
        protected InventoryService $inventory,
        protected PurchaseOrderService $purchaseOrders,
    ) {}

    /**
     * @param  array<int, array{purchase_order_item_id:int,received_qty:float,damaged_qty:float}>  $items
     *
     * @throws \InvalidArgumentException if any item's damaged_qty exceeds its received_qty.
     */
    public function create(PurchaseOrder $po, array $data, array $items): GoodsReceive
    {
        return DB::transaction(function () use ($po, $data, $items) {
            $grn = $po->goodsReceives()->create([
                'grn_no' => $this->numbering->next('GRN'),
                'received_date' => $data['received_date'] ?? now()->toDateString(),
                'received_by' => $data['received_by'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $receivedQty = (float) ($item['received_qty'] ?? 0);
                $damagedQty = (float) ($item['damaged_qty'] ?? 0);

                if ($damagedQty > $receivedQty) {
                    throw new \InvalidArgumentException('Damaged quantity cannot exceed received quantity.');
                }

                $poItem = PurchaseOrderItem::where('purchase_order_id', $po->id)
                    ->findOrFail($item['purchase_order_item_id']);

                $acceptedQty = $receivedQty - $damagedQty;

                $grnItem = $grn->items()->create([
                    'purchase_order_item_id' => $poItem->id,
                    'ordered_qty' => $poItem->qty,
                    'received_qty' => $receivedQty,
                    'damaged_qty' => $damagedQty,
                    'accepted_qty' => $acceptedQty,
                ]);

                if ($acceptedQty > 0) {
                    $this->inventory->recordMovement(
                        $poItem->product,
                        $po->warehouse,
                        StockMovementType::Purchase,
                        $acceptedQty,
                        reference: $grn,
                        createdBy: $data['received_by'] ?? null,
                    );
                }

                unset($grnItem);
            }

            $this->purchaseOrders->recalculateReceiptStatus($po);

            return $grn->fresh(['items']);
        });
    }
}
