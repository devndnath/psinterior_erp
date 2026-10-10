<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * The single, sole writer of `warehouse_stocks` (spec §24). Every other
 * service, controller or Livewire component that needs to move stock
 * MUST call recordMovement() (or one of its convenience wrappers) here —
 * never update warehouse_stocks directly. That's what keeps the running
 * balance and the stock_movements ledger from ever drifting apart.
 *
 * Concurrency-safety mirrors NumberingService::next() exactly: the
 * warehouse_stocks row is locked with lockForUpdate() inside a
 * transaction before it is read or written, so two requests racing to
 * move the same product/warehouse pair's stock can never both read a
 * stale quantity.
 */
class InventoryService
{
    /**
     * @throws \RuntimeException if the resulting quantity would go
     *                            negative and $allowNegative is false.
     */
    public function recordMovement(
        Product $product,
        Warehouse $warehouse,
        StockMovementType $type,
        float $signedQuantity,
        ?Model $reference = null,
        ?int $createdBy = null,
        bool $allowNegative = false,
    ): StockMovement {
        return DB::transaction(function () use ($product, $warehouse, $type, $signedQuantity, $reference, $createdBy, $allowNegative) {
            // Self-registers the row on first use, the same way
            // NumberingService self-registers a sequence key — a new
            // product/warehouse pair never needs a manual seeder entry
            // before stock can move against it.
            $stock = WarehouseStock::firstOrCreate(
                ['warehouse_id' => $warehouse->id, 'product_id' => $product->id],
                ['quantity' => 0],
            );

            $stock = WarehouseStock::query()
                ->where('id', $stock->id)
                ->lockForUpdate()
                ->first();

            $currentQuantity = (float) $stock->quantity;
            $newQuantity = $currentQuantity + $signedQuantity;

            if ($newQuantity < 0 && ! $allowNegative) {
                throw new \RuntimeException(
                    "Insufficient stock of {$product->name} in {$warehouse->name}: ".
                    "{$currentQuantity} available, {$signedQuantity} requested."
                );
            }

            $stock->update(['quantity' => $newQuantity]);

            return StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'type' => $type->value,
                'quantity' => $signedQuantity,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'balance_after' => $newQuantity,
                'created_by' => $createdBy,
            ]);
        });
    }

    public function openingStock(Product $product, Warehouse $warehouse, float $quantity, ?int $createdBy = null): StockMovement
    {
        return $this->recordMovement($product, $warehouse, StockMovementType::Opening, $quantity, createdBy: $createdBy);
    }

    /**
     * A deliberate, manual correction — allowed to push stock negative
     * (e.g. correcting an earlier over-count) since it's an explicit
     * human decision, not an automatic Issue/Damage/TransferOut.
     */
    public function adjust(Product $product, Warehouse $warehouse, float $signedQuantity, ?int $createdBy = null): StockMovement
    {
        return $this->recordMovement($product, $warehouse, StockMovementType::Adjustment, $signedQuantity, createdBy: $createdBy, allowNegative: true);
    }

    public function currentStock(Product $product, Warehouse $warehouse): float
    {
        return (float) WarehouseStock::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->value('quantity') ?? 0.0;
    }
}
