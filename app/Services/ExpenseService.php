<?php

namespace App\Services;

use App\Models\Expense;

/**
 * Owns the Expense aggregate. Deliberately simple — no side effects onto
 * any other aggregate (unlike Payments, which feed back into an
 * Invoice's paid_amount); an expense only reduces profit, computed on
 * the fly by ProfitabilityService, not stored anywhere.
 */
class ExpenseService
{
    public function __construct(protected NumberingService $numbering) {}

    public function create(array $data): Expense
    {
        return Expense::create([
            'expense_no' => $this->numbering->next('EXP'),
            'project_id' => $data['project_id'],
            'category_id' => $data['category_id'],
            'vendor_name' => $data['vendor_name'] ?? null,
            'vendor_id' => $data['vendor_id'] ?? null,
            'amount' => $data['amount'],
            'date' => $data['date'],
            'method' => $data['method'],
            'description' => $data['description'] ?? null,
            'created_by' => $data['created_by'] ?? null,
        ]);
    }

    public function update(Expense $expense, array $data): Expense
    {
        $expense->update([
            'project_id' => $data['project_id'] ?? $expense->project_id,
            'category_id' => $data['category_id'] ?? $expense->category_id,
            'vendor_name' => $data['vendor_name'] ?? $expense->vendor_name,
            'vendor_id' => array_key_exists('vendor_id', $data) ? $data['vendor_id'] : $expense->vendor_id,
            'amount' => $data['amount'] ?? $expense->amount,
            'date' => $data['date'] ?? $expense->date,
            'method' => $data['method'] ?? $expense->method->value,
            'description' => $data['description'] ?? $expense->description,
        ]);

        return $expense->fresh();
    }
}
