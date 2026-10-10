<?php

namespace App\Livewire\Reports;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Hub page linking to the five sub-reports (ARCHITECTURE.md §6 point
 * 11). Gated at the route-middleware level (`can:view_reports`), the
 * same pattern Profitability/Index uses for `view_finance` — there's no
 * model to key a Policy off a report page.
 */
#[Layout('layouts.admin', ['title' => 'Reports'])]
#[Title('Reports')]
class Index extends Component
{
    public function render()
    {
        $reports = [
            ['label' => 'Project Status', 'route' => 'reports.project-status', 'description' => 'Every project — status, progress, contract value and dates.'],
            ['label' => 'Sales Funnel', 'route' => 'reports.sales-funnel', 'description' => 'Leads by status and source, with conversion rate.'],
            ['label' => 'Financial Summary', 'route' => 'reports.financial-summary', 'description' => 'Invoiced, received, outstanding, expenses and invoice ageing.'],
            ['label' => 'Inventory', 'route' => 'reports.inventory', 'description' => 'Stock on hand per product/warehouse and low-stock items.'],
            ['label' => 'HR', 'route' => 'reports.hr', 'description' => 'Attendance summary and pending leave approvals.'],
        ];

        return view('livewire.reports.index', ['reports' => $reports]);
    }
}
