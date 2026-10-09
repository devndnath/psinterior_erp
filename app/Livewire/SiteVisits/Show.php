<?php

namespace App\Livewire\SiteVisits;

use App\Models\SiteVisit;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public SiteVisit $siteVisit;

    public function mount(SiteVisit $siteVisit): void
    {
        Gate::authorize('view', $siteVisit);
        $this->siteVisit = $siteVisit;
    }

    #[Title('Site Visit')]
    public function render()
    {
        return view('livewire.site-visits.show', [
            'items' => $this->siteVisit->items,
            'photos' => $this->siteVisit->documents()->where('category', 'site_photo')->get(),
        ]);
    }
}
