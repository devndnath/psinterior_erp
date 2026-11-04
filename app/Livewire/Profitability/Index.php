<?php

namespace App\Livewire\Profitability;

use App\Services\ProfitabilityService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Report page, not a CRUD resource — gated at the route-middleware level
 * (`can:view_finance`, routes/web.php) the same way Settings is gated on
 * `manage_settings`, since there's no model to key a Policy off.
 */
#[Layout('layouts.admin', ['title' => 'Profitability'])]
#[Title('Profitability')]
class Index extends Component
{
    public function mount(): void
    {
        // Also gated by route middleware; checked here too so the
        // component can't be mounted without the ability.
        abort_unless(auth()->user()?->can('view_finance'), 403);
    }

    public function render(ProfitabilityService $service)
    {
        return view('livewire.profitability.index', [
            'rows' => $service->forAllProjects(),
        ]);
    }
}
