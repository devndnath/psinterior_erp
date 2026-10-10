<?php

namespace App\Livewire\Units;

use App\Models\Unit;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin-editable reference list — gated entirely on `manage_settings`.
 * No Show page, just Index + Form.
 */
#[Layout('layouts.admin', ['title' => 'Units'])]
#[Title('Units')]
class Index extends Component
{
    use WithPagination;

    public function mount(): void
    {
        Gate::authorize('viewAny', Unit::class);
    }

    public function deleteUnit(int $unitId): void
    {
        $unit = Unit::findOrFail($unitId);
        Gate::authorize('delete', $unit);

        $unit->delete();

        $this->dispatch('toast', message: "Unit \"{$unit->name}\" deleted.", type: 'success');
    }

    public function render()
    {
        return view('livewire.units.index', [
            'units' => Unit::orderBy('name')->paginate(15),
        ]);
    }
}
