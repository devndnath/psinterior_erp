<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Enums\StockTransferStatus;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;

/**
 * Owns the Stock Transfer aggregate. Creating or shipping a transfer
 * never moves stock — a transfer in transit hasn't arrived yet (a
 * deliberate simplification: this codebase tracks "in transit" as a
 * status flag only, not a third stock location). Only complete() calls
 * into InventoryService, exactly twice per item (TransferOut at the
 * source, TransferIn at the destination).
 */
class StockTransferService
{
    public function __construct(
        protected NumberingService $numbering,
        protected InventoryService $inventory,
    ) {}

    public function create(array $data, array $items): StockTransfer
    {
        return DB::transaction(function () use ($data, $items) {
            $transfer = StockTransfer::create([
                'transfer_no' => $this->numbering->next('ST'),
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'notes' => $data['notes'] ?? null,
                'status' => StockTransferStatus::Pending->value,
            ]);

            foreach ($items as $item) {
                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => (float) $item['quantity'],
                ]);
            }

            return $transfer->fresh(['items']);
        });
    }

    public function ship(StockTransfer $transfer): StockTransfer
    {
        $transfer->update(['status' => StockTransferStatus::InTransit->value]);

        return $transfer->fresh();
    }

    /**
     * Moves stock out of the source and into the destination for every
     * item. If any item's source stock would go negative,
     * InventoryService throws and the whole transaction (including any
     * items already moved in this same call) rolls back — the exception
     * is left to propagate so the caller (a Livewire component) can show
     * it as a validation error.
     */
    public function complete(StockTransfer $transfer, ?int $userId = null): StockTransfer
    {
        return DB::transaction(function () use ($transfer, $userId) {
            $transfer->load(['items.product', 'fromWarehouse', 'toWarehouse']);

            foreach ($transfer->items as $item) {
                $this->inventory->recordMovement(
                    $item->product,
                    $transfer->fromWarehouse,
                    StockMovementType::TransferOut,
                    -$item->quantity,
                    reference: $transfer,
                    createdBy: $userId,
                );

                $this->inventory->recordMovement(
                    $item->product,
                    $transfer->toWarehouse,
                    StockMovementType::TransferIn,
                    (float) $item->quantity,
                    reference: $transfer,
                    createdBy: $userId,
                );
            }

            $transfer->update(['status' => StockTransferStatus::Completed->value]);

            return $transfer->fresh();
        });
    }

    /**
     * Only allowed while Pending or InTransit — once Completed, the
     * stock has already moved and a transfer is never un-done here (it
     * would need a reverse transfer instead).
     */
    public function cancel(StockTransfer $transfer): StockTransfer
    {
        if ($transfer->status === StockTransferStatus::Completed) {
            throw new \RuntimeException('A completed stock transfer cannot be cancelled.');
        }

        $transfer->update(['status' => StockTransferStatus::Cancelled->value]);

        return $transfer->fresh();
    }
}
