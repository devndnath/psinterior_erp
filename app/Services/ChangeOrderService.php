<?php

namespace App\Services;

use App\Enums\ApprovalDecision;
use App\Enums\ChangeOrderStatus;
use App\Models\ChangeOrder;
use App\Models\Project;
use App\Notifications\ChangeOrderApprovedNotification;
use Illuminate\Support\Facades\DB;

/**
 * Owns the Change Order aggregate. Recording the client's decision is
 * the one action with a side effect outside this aggregate: an approved
 * change order adds its `cost` straight onto the project's
 * `contract_value` (ARCHITECTURE.md §3.6 / spec §19) — the same field
 * ContractService::activate() stamps when a contract goes live.
 */
class ChangeOrderService
{
    public function __construct(protected NumberingService $numbering) {}

    public function create(Project $project, array $data, array $items = []): ChangeOrder
    {
        return DB::transaction(function () use ($project, $data, $items) {
            $changeOrder = $project->changeOrders()->create([
                'co_no' => $this->numbering->next('CO'),
                'description' => $data['description'] ?? null,
                'cost' => $data['cost'] ?? 0,
                'status' => ChangeOrderStatus::Pending->value,
            ]);

            $this->syncItems($changeOrder, $items);

            if (! empty($items)) {
                $this->recalculateCost($changeOrder);
            }

            return $changeOrder->fresh(['items']);
        });
    }

    public function update(ChangeOrder $changeOrder, array $data, array $items): ChangeOrder
    {
        return DB::transaction(function () use ($changeOrder, $data, $items) {
            $changeOrder->update([
                'description' => $data['description'] ?? $changeOrder->description,
                'cost' => $data['cost'] ?? $changeOrder->cost,
            ]);

            $this->syncItems($changeOrder, $items);

            if (! empty($items)) {
                $this->recalculateCost($changeOrder);
            }

            return $changeOrder->fresh(['items']);
        });
    }

    /**
     * Records the client's decision on this change order. Approving it
     * is the one path that mutates the project's contract_value — done
     * inside the same transaction so the two numbers never drift apart.
     */
    public function recordClientDecision(ChangeOrder $changeOrder, string $decision): ChangeOrder
    {
        return DB::transaction(function () use ($changeOrder, $decision) {
            $status = match ($decision) {
                ApprovalDecision::Approved->value => ChangeOrderStatus::Approved,
                ApprovalDecision::Rejected->value => ChangeOrderStatus::Rejected,
                default => ChangeOrderStatus::Pending,
            };

            $changeOrder->update([
                'client_approval_status' => $decision,
                'status' => $status->value,
                'approved_at' => $status === ChangeOrderStatus::Approved ? now() : null,
            ]);

            if ($status === ChangeOrderStatus::Approved) {
                $project = $changeOrder->project;
                $project->update([
                    'contract_value' => (float) ($project->contract_value ?? 0) + (float) $changeOrder->cost,
                ]);

                // Database-channel notification only (Phase 11).
                $project->projectManager?->notify(new ChangeOrderApprovedNotification($changeOrder));
            }

            return $changeOrder->fresh();
        });
    }

    protected function syncItems(ChangeOrder $changeOrder, array $items): void
    {
        $changeOrder->items()->delete();

        foreach ($items as $item) {
            $qty = (float) ($item['qty'] ?? 1);
            $rate = (float) ($item['rate'] ?? 0);

            $changeOrder->items()->create([
                'description' => $item['description'],
                'qty' => $qty,
                'rate' => $rate,
                'amount' => round($qty * $rate, 2),
            ]);
        }
    }

    protected function recalculateCost(ChangeOrder $changeOrder): void
    {
        $changeOrder->update(['cost' => (float) $changeOrder->items()->sum('amount')]);
    }
}
