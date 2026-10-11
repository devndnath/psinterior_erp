<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Quotation;
use Livewire\Component;

/**
 * Phase 14: real global search for the topbar, replacing the disabled
 * "Search coming soon…" placeholder input (confirmed dead by the Phase
 * 13 audit). Searches a small, high-value set of models — Leads,
 * Clients, Projects, Invoices, Quotations — and, per model, only runs
 * the query at all when the current user holds that model's existing
 * `view_*` ability (the same abilities the sidebar/routes already gate
 * on), so a result never leaks something the viewer couldn't otherwise
 * open. Each model is capped at 5 rows; results are grouped by type.
 */
class GlobalSearch extends Component
{
    public string $query = '';

    public function getResultsProperty(): array
    {
        $term = trim($this->query);

        if (mb_strlen($term) < 2) {
            return [];
        }

        $groups = [];

        $user = auth()->user();

        if ($user?->can('view_leads')) {
            $leads = Lead::query()
                ->where(fn ($q) => $q
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                )
                ->orderBy('name')
                ->limit(5)
                ->get(['id', 'name', 'phone']);

            if ($leads->isNotEmpty()) {
                $groups[] = [
                    'label' => 'Leads',
                    'items' => $leads->map(fn ($lead) => [
                        'title' => $lead->name,
                        'subtitle' => $lead->phone,
                        'url' => route('leads.show', $lead),
                    ])->all(),
                ];
            }
        }

        if ($user?->can('view_clients')) {
            $clients = Client::query()
                ->where('name', 'like', "%{$term}%")
                ->orderBy('name')
                ->limit(5)
                ->get(['id', 'name']);

            if ($clients->isNotEmpty()) {
                $groups[] = [
                    'label' => 'Clients',
                    'items' => $clients->map(fn ($client) => [
                        'title' => $client->name,
                        'subtitle' => null,
                        'url' => route('clients.show', $client),
                    ])->all(),
                ];
            }
        }

        if ($user?->can('view_projects')) {
            $projects = Project::query()
                ->where(fn ($q) => $q
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('project_code', 'like', "%{$term}%")
                )
                ->orderBy('name')
                ->limit(5)
                ->get(['id', 'name', 'project_code']);

            if ($projects->isNotEmpty()) {
                $groups[] = [
                    'label' => 'Projects',
                    'items' => $projects->map(fn ($project) => [
                        'title' => $project->name,
                        'subtitle' => $project->project_code,
                        'url' => route('projects.show', $project),
                    ])->all(),
                ];
            }
        }

        if ($user?->can('view_invoices')) {
            $invoices = Invoice::query()
                ->where('invoice_no', 'like', "%{$term}%")
                ->latest('invoice_date')
                ->limit(5)
                ->get(['id', 'invoice_no']);

            if ($invoices->isNotEmpty()) {
                $groups[] = [
                    'label' => 'Invoices',
                    'items' => $invoices->map(fn ($invoice) => [
                        'title' => $invoice->invoice_no,
                        'subtitle' => null,
                        'url' => route('invoices.show', $invoice),
                    ])->all(),
                ];
            }
        }

        if ($user?->can('view_quotations')) {
            $quotations = Quotation::query()
                ->where('quotation_no', 'like', "%{$term}%")
                ->latest('created_at')
                ->limit(5)
                ->get(['id', 'quotation_no']);

            if ($quotations->isNotEmpty()) {
                $groups[] = [
                    'label' => 'Quotations',
                    'items' => $quotations->map(fn ($quotation) => [
                        'title' => $quotation->quotation_no,
                        'subtitle' => null,
                        'url' => route('quotations.show', $quotation),
                    ])->all(),
                ];
            }
        }

        return $groups;
    }

    public function render()
    {
        return view('livewire.global-search', [
            'results' => $this->getResultsProperty(),
        ]);
    }
}
