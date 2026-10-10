<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Support\Collection;

/**
 * Read-only reporting over the Finance aggregates (ARCHITECTURE.md §3.7
 * / the Profitability report). Profit is deliberately computed as
 * contract_value - total_expenses rather than total_invoiced -
 * total_expenses: invoicing is a cash-flow/billing concern, while the
 * contract value is what the project actually promised to deliver for —
 * the two can legitimately differ mid-project without that meaning the
 * job became more or less profitable.
 */
class ProfitabilityService
{
    public function forProject(Project $project): array
    {
        $contractValue = (float) ($project->contract_value ?? 0);

        $totalInvoiced = (float) Invoice::where('project_id', $project->id)
            ->where('status', '!=', InvoiceStatus::Cancelled->value)
            ->sum('grand_total');

        $totalReceived = (float) Payment::where('project_id', $project->id)->sum('amount');

        $totalExpenses = (float) Expense::where('project_id', $project->id)->sum('amount');

        $profit = $contractValue - $totalExpenses;

        return [
            'contract_value' => $contractValue,
            'total_invoiced' => $totalInvoiced,
            'total_received' => $totalReceived,
            'total_expenses' => $totalExpenses,
            'profit' => $profit,
            'margin_percent' => $contractValue > 0 ? round($profit / $contractValue * 100, 2) : null,
        ];
    }

    /**
     * Same shape as forProject(), for every project at once — built with
     * aggregate subqueries rather than calling forProject() in a loop, so
     * the Profitability report page issues a constant number of queries
     * regardless of how many projects exist.
     */
    public function forAllProjects(): Collection
    {
        $invoicedByProject = Invoice::where('status', '!=', InvoiceStatus::Cancelled->value)
            ->selectRaw('project_id, SUM(grand_total) as total')
            ->groupBy('project_id')
            ->pluck('total', 'project_id');

        $receivedByProject = Payment::selectRaw('project_id, SUM(amount) as total')
            ->groupBy('project_id')
            ->pluck('total', 'project_id');

        $expensesByProject = Expense::selectRaw('project_id, SUM(amount) as total')
            ->groupBy('project_id')
            ->pluck('total', 'project_id');

        return Project::query()->orderBy('name')->get()->map(function (Project $project) use (
            $invoicedByProject, $receivedByProject, $expensesByProject
        ) {
            $contractValue = (float) ($project->contract_value ?? 0);
            $totalExpenses = (float) ($expensesByProject[$project->id] ?? 0);
            $profit = $contractValue - $totalExpenses;

            return [
                'project' => $project,
                'contract_value' => $contractValue,
                'total_invoiced' => (float) ($invoicedByProject[$project->id] ?? 0),
                'total_received' => (float) ($receivedByProject[$project->id] ?? 0),
                'total_expenses' => $totalExpenses,
                'profit' => $profit,
                'margin_percent' => $contractValue > 0 ? round($profit / $contractValue * 100, 2) : null,
            ];
        });
    }
}
