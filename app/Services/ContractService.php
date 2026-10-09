<?php

namespace App\Services;

use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\Project;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;

/**
 * Owns the Contract aggregate. A contract is normally drawn up from an
 * approved Quotation (createFromQuotation copies its items and total
 * across) but can also be created standalone. Activating a contract
 * stamps the project's `contract_value` — the figure every later
 * profitability/finance calculation reads (ARCHITECTURE.md §3.3/§3.6).
 */
class ContractService
{
    public function __construct(protected NumberingService $numbering) {}

    public function create(Project $project, array $data, array $items = []): Contract
    {
        return DB::transaction(function () use ($project, $data, $items) {
            $contract = $project->contracts()->create([
                'contract_no' => $this->numbering->next('CON'),
                'client_id' => $data['client_id'] ?? $project->client_id,
                'quotation_id' => $data['quotation_id'] ?? null,
                'amount' => $data['amount'] ?? 0,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'payment_terms' => $data['payment_terms'] ?? null,
                'warranty_terms' => $data['warranty_terms'] ?? null,
                'terms' => $data['terms'] ?? null,
                'status' => $data['status'] ?? ContractStatus::Draft->value,
            ]);

            $this->syncItems($contract, $items);

            if ($contract->status === ContractStatus::Active) {
                $this->activate($contract);
            }

            return $contract->fresh(['items']);
        });
    }

    public function createFromQuotation(Quotation $quotation, array $data = []): Contract
    {
        $items = $quotation->items->map(fn ($item) => [
            'description' => $item->description,
            'amount' => $item->amount,
        ])->all();

        return $this->create($quotation->project, array_merge([
            'client_id' => $quotation->client_id,
            'quotation_id' => $quotation->id,
            'amount' => $quotation->grand_total,
        ], $data), $items);
    }

    public function update(Contract $contract, array $data, array $items): Contract
    {
        return DB::transaction(function () use ($contract, $data, $items) {
            $contract->update([
                'amount' => $data['amount'] ?? $contract->amount,
                'start_date' => $data['start_date'] ?? $contract->start_date,
                'end_date' => $data['end_date'] ?? $contract->end_date,
                'payment_terms' => $data['payment_terms'] ?? $contract->payment_terms,
                'warranty_terms' => $data['warranty_terms'] ?? $contract->warranty_terms,
                'terms' => $data['terms'] ?? $contract->terms,
            ]);

            $this->syncItems($contract, $items);

            return $contract->fresh(['items']);
        });
    }

    /**
     * Marks this contract Active, flips any other Active contract on the
     * same project to Completed (a project effectively has one active
     * contract at a time — ARCHITECTURE.md §2), and stamps the project's
     * `contract_value` so Phase 3's project record reflects the real
     * commercial figure.
     */
    public function activate(Contract $contract): Contract
    {
        return DB::transaction(function () use ($contract) {
            Contract::where('project_id', $contract->project_id)
                ->where('id', '!=', $contract->id)
                ->where('status', ContractStatus::Active->value)
                ->update(['status' => ContractStatus::Completed->value]);

            $contract->update(['status' => ContractStatus::Active->value]);
            $contract->project->update(['contract_value' => $contract->amount]);

            return $contract->fresh();
        });
    }

    protected function syncItems(Contract $contract, array $items): void
    {
        $contract->items()->delete();

        foreach ($items as $item) {
            $contract->items()->create([
                'description' => $item['description'],
                'amount' => $item['amount'] ?? 0,
            ]);
        }
    }
}
