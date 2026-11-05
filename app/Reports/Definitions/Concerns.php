<?php

namespace App\Reports\Definitions;

use App\Enums\InvoiceStatus;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Reports\Filters;
use Illuminate\Support\Collection;

/**
 * Per-project money lookups shared by the project reports: invoiced
 * (non-cancelled), received and spent, keyed by project id. Received and
 * spent respect the date range; budget figures are lifetime.
 */
trait Concerns
{
    protected function projectsFor(Filters $f): Collection
    {
        return Project::query()
            ->when($f->project, fn ($q) => $q->whereKey($f->project))
            ->when($f->client, fn ($q) => $q->where('client_id', $f->client))
            ->with('client:id,name')->orderBy('project_code')->get();
    }

    protected function moneyByProject(Collection $ids, Filters $f): array
    {
        return [
            'invoiced' => $f->dates(Invoice::whereIn('project_id', $ids)->where('status', '!=', InvoiceStatus::Cancelled->value), 'invoice_date')
                ->selectRaw('project_id, SUM(grand_total) as v')->groupBy('project_id')->pluck('v', 'project_id'),
            'received' => $f->dates(Payment::whereIn('project_id', $ids), 'date')
                ->selectRaw('project_id, SUM(amount) as v')->groupBy('project_id')->pluck('v', 'project_id'),
            'spent' => $f->dates(Expense::whereIn('project_id', $ids), 'date')
                ->selectRaw('project_id, SUM(amount) as v')->groupBy('project_id')->pluck('v', 'project_id'),
        ];
    }

    protected static function budgetOf(Project $p): float
    {
        return (float) ($p->contract_value ?? $p->estimated_budget ?? 0);
    }
}
