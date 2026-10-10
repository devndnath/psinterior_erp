<?php

namespace App\Services;

use App\Enums\MaterialRequisitionStatus;
use App\Enums\StockMovementType;
use App\Models\MaterialRequisition;
use App\Models\Project;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

/**
 * Owns the Material Requisition aggregate and its approval chain:
 * Pending -> ManagerApproved -> StoreApproved -> Issued, with Rejected
 * reachable from Pending or ManagerApproved. Only issue() moves stock —
 * every earlier step is a pure status transition.
 *
 * Partial issuance is out of scope for this phase: issue() always issues
 * an item's full requested quantity (documented as a simplification).
 */
class MaterialRequisitionService
{
    public function __construct(
        protected NumberingService $numbering,
        protected InventoryService $inventory,
    ) {}

    public function create(Project $project, array $data, array $items): MaterialRequisition
    {
        return DB::transaction(function () use ($project, $data, $items) {
            $mr = $project->materialRequisitions()->create([
                'mr_no' => $this->numbering->next('MR'),
                'requested_by' => $data['requested_by'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'date' => $data['date'],
                'purpose' => $data['purpose'] ?? null,
                'status' => MaterialRequisitionStatus::Pending->value,
            ]);

            foreach ($items as $item) {
                $mr->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => (float) $item['quantity'],
                    'issued_quantity' => 0,
                ]);
            }

            return $mr->fresh(['items']);
        });
    }

    /**
     * Edits are only meaningful while the requisition hasn't moved past
     * Pending — enforced by MaterialRequisitionPolicy::update() at the
     * Livewire layer, not here; this method just re-syncs the header and
     * item rows the same way ChangeOrderService::update() does.
     */
    public function update(MaterialRequisition $mr, array $data, array $items): MaterialRequisition
    {
        return DB::transaction(function () use ($mr, $data, $items) {
            $mr->update([
                'warehouse_id' => $data['warehouse_id'] ?? $mr->warehouse_id,
                'date' => $data['date'] ?? $mr->date,
                'purpose' => $data['purpose'] ?? $mr->purpose,
            ]);

            $mr->items()->delete();

            foreach ($items as $item) {
                $mr->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => (float) $item['quantity'],
                    'issued_quantity' => 0,
                ]);
            }

            return $mr->fresh(['items']);
        });
    }

    public function approveByManager(MaterialRequisition $mr): MaterialRequisition
    {
        $this->assertStatus($mr, MaterialRequisitionStatus::Pending);

        $mr->update(['status' => MaterialRequisitionStatus::ManagerApproved->value]);

        return $mr->fresh();
    }

    public function approveByStore(MaterialRequisition $mr): MaterialRequisition
    {
        $this->assertStatus($mr, MaterialRequisitionStatus::ManagerApproved);

        $mr->update(['status' => MaterialRequisitionStatus::StoreApproved->value]);

        return $mr->fresh();
    }

    public function reject(MaterialRequisition $mr): MaterialRequisition
    {
        if (! in_array($mr->status, [MaterialRequisitionStatus::Pending, MaterialRequisitionStatus::ManagerApproved], true)) {
            throw new \RuntimeException('Only a pending or manager-approved requisition can be rejected.');
        }

        $mr->update(['status' => MaterialRequisitionStatus::Rejected->value]);

        return $mr->fresh();
    }

    /**
     * Resolves the source warehouse ($warehouseId takes precedence, then
     * $mr->warehouse_id, else throws) and, inside one transaction, issues
     * every item's full quantity via InventoryService — the Issue
     * movement that actually decrements stock.
     */
    public function issue(MaterialRequisition $mr, ?int $warehouseId = null, ?int $userId = null): MaterialRequisition
    {
        $this->assertStatus($mr, MaterialRequisitionStatus::StoreApproved);

        $resolvedWarehouseId = $warehouseId ?: $mr->warehouse_id;

        if (! $resolvedWarehouseId) {
            throw new \RuntimeException('A warehouse must be chosen before this requisition can be issued.');
        }

        return DB::transaction(function () use ($mr, $resolvedWarehouseId, $userId) {
            $warehouse = Warehouse::findOrFail($resolvedWarehouseId);
            $mr->load('items.product');

            foreach ($mr->items as $item) {
                $this->inventory->recordMovement(
                    $item->product,
                    $warehouse,
                    StockMovementType::Issue,
                    -$item->quantity,
                    reference: $mr,
                    createdBy: $userId,
                );

                $item->update(['issued_quantity' => $item->quantity]);
            }

            $mr->update([
                'status' => MaterialRequisitionStatus::Issued->value,
                'warehouse_id' => $warehouse->id,
            ]);

            return $mr->fresh(['items']);
        });
    }

    protected function assertStatus(MaterialRequisition $mr, MaterialRequisitionStatus $expected): void
    {
        if ($mr->status !== $expected) {
            throw new \RuntimeException("This requisition must be {$expected->label()} for this action.");
        }
    }
}
