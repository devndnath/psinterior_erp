<?php

namespace App\Livewire\Client\SiteReports;

use App\Models\SiteReport;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Ownership check, not a staff Policy — see "Portal authorization
 * model" in PHASE11_SUMMARY.md.
 */
#[Layout('layouts.client-portal')]
class Show extends Component
{
    public SiteReport $siteReport;

    public function mount(SiteReport $siteReport): void
    {
        $client = Auth::guard('client')->user()->client;
        abort_unless($client, 403, 'No client record linked to this account.');
        abort_unless($siteReport->project && $siteReport->project->client_id === $client->id, 403);

        $this->siteReport = $siteReport;
    }

    #[Title('Site Report')]
    public function render()
    {
        $this->siteReport->load(['project']);

        return view('livewire.client.site-reports.show', [
            'photos' => $this->siteReport->documents()->where('category', 'site_report_photo')->get(),
        ]);
    }
}
