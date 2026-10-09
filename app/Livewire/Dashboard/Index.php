<?php

namespace App\Livewire\Dashboard;

use App\Enums\LeadStatus;
use App\Models\Client;
use App\Models\Lead;
use App\Models\SiteVisit;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Role-aware dashboard. Phase 1 shipped an empty shell; Phase 2 adds the
 * first real KPI cards (Leads/Clients/Site Visits — spec §4), each gated
 * by the same permission its own module uses so a user without
 * `view_leads` simply doesn't see that card. Project/finance/inventory
 * KPIs and charts are added as those modules ship in later phases.
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
        ]);
    }
}
