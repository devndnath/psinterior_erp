<?php

namespace App\Livewire\Client;

use App\Enums\DesignStatus;
use App\Enums\InvoiceStatus;
use App\Enums\ProjectStatus;
use App\Enums\QuotationStatus;
use App\Models\Design;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Quotation;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Client Portal dashboard — every figure is scoped to the logged-in
 * client's OWN records (see "Portal authorization model" in
 * PHASE11_SUMMARY.md: a plain ownership check, never a staff Policy or
 * the staff permission-check methods, since portal users hold no
 * Spatie permissions at all).
 */
#[Layout('layouts.client-portal', ['title' => 'Dashboard'])]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function mount(): void
    {
        abort_unless(Auth::guard('client')->user()->client, 403, 'No client record linked to this account.');
    }

    public function render()
    {
        $client = Auth::guard('client')->user()->client;
        $projectIds = Project::where('client_id', $client->id)->pluck('id');

        return view('livewire.client.dashboard', [
            'activeProjects' => Project::where('client_id', $client->id)
                ->whereNotIn('status', [ProjectStatus::Completed->value, ProjectStatus::Cancelled->value])
                ->count(),
            'pendingDesignApprovals' => Design::whereIn('project_id', $projectIds)
                ->whereIn('status', [DesignStatus::Submitted->value, DesignStatus::UnderReview->value])
                ->count(),
            'pendingQuotationApprovals' => Quotation::where('client_id', $client->id)
                ->whereIn('status', [QuotationStatus::Sent->value, QuotationStatus::Viewed->value, QuotationStatus::Negotiation->value])
                ->count(),
            'outstandingInvoiceTotal' => Invoice::where('client_id', $client->id)
                ->whereIn('status', [InvoiceStatus::Issued->value, InvoiceStatus::PartiallyPaid->value, InvoiceStatus::Overdue->value])
                ->get()
                ->sum(fn (Invoice $invoice) => $invoice->outstandingAmount()),
        ]);
    }
}
