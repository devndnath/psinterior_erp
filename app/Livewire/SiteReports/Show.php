<?php

namespace App\Livewire\SiteReports;

use App\Models\SiteReport;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public SiteReport $siteReport;

    public function mount(SiteReport $siteReport): void
    {
        Gate::authorize('view', $siteReport);
        $this->siteReport = $siteReport;
    }

    #[Title('Site Report')]
    public function render()
    {
        $this->siteReport->load(['project', 'reporter']);

        return view('livewire.site-reports.show', [
            'photos' => $this->siteReport->documents()->where('category', 'site_report_photo')->get(),
        ]);
    }
}
