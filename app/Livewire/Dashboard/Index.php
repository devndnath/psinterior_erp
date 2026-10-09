<?php

namespace App\Livewire\Dashboard;

use App\Enums\ChangeOrderStatus;
use App\Enums\DesignStatus;
use App\Enums\LeadStatus;
use App\Enums\ProjectStatus;
use App\Enums\QuotationStatus;
use App\Models\ChangeOrder;
use App\Models\Client;
use App\Models\Design;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\SiteVisit;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Role-aware dashboard. Phase 1 shipped an empty shell; Phase 2 added the
 * CRM KPI cards; Phase 3 added Projects; Phase 4 added Designs awaiting a
 * client decision; Phase 5 adds Quotations awaiting a decision and
 * pending Change Orders (spec §4), each card gated by the same
 * permission its own module uses so a user without `view_leads` simply
 * doesn't see that card. Finance/inventory KPIs and charts are added as
 * those modules ship in later phases.
 */
#[Layout('layouts.admin', ['title' => 'Dashboard'])]
#[Title('Dashboard')]
class Index extends Component
{
    public function render()
    {
        $user = Auth::user();

        return view('livewire.dashboard.index', [
            'totalLeads' => $user->can('view_leads') ? Lead::count() : null,
            'newLeads' => $user->can('view_leads') ? Lead::where('status', LeadStatus::New)->count() : null,
            'openLeads' => $user->can('view_leads') ? Lead::whereIn('status', array_filter(
                LeadStatus::cases(),
                fn (LeadStatus $s) => $s->isOpen(),
            ))->count() : null,
            'totalClients' => $user->can('view_clients') ? Client::count() : null,
            'upcomingSiteVisits' => $user->can('view_site_visits')
                ? SiteVisit::where('visit_date', '>=', now()->toDateString())->count()
                : null,
            'activeProjects' => $user->can('view_projects') ? Project::whereNotIn('status', [
                ProjectStatus::Completed->value,
                ProjectStatus::Cancelled->value,
            ])->count() : null,
            'projectsOnHold' => $user->can('view_projects') ? Project::where('status', ProjectStatus::OnHold->value)->count() : null,
            'designsAwaitingDecision' => $user->can('view_designs') ? Design::whereIn('status', [
                DesignStatus::Submitted->value,
                DesignStatus::UnderReview->value,
            ])->count() : null,
            'quotationsAwaitingDecision' => $user->can('view_quotations') ? Quotation::whereIn('status', [
                QuotationStatus::Sent->value,
                QuotationStatus::Viewed->value,
                QuotationStatus::Negotiation->value,
            ])->count() : null,
            'changeOrdersPending' => $user->can('view_change_orders') ? ChangeOrder::where('status', ChangeOrderStatus::Pending->value)->count() : null,
        ]);
    }
}
