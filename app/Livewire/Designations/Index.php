<?php

namespace App\Livewire\Designations;

use App\Models\Designation;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Same audit gap and shape as Departments\Index — Designation has
 * existed since Phase 1 (always scoped under a Department) with no
 * screen to manage it.
 */
#[Layout('layouts.admin', ['title' => 'Designations'])]
#[Title('Designations')]
class Index extends Component
{
    use WithPagination;

    public function mount(): void
    {
        Gate::authorize('viewAny', Designation::class);
    }

    public function deleteDesignation(int $designationId): void
    {
        $designation = Designation::findOrFail($designationId);
        Gate::authorize('delete', $designation);

        if ($designation->employees()->exists()) {
            $this->dispatch('toast', message: "\"{$designation->name}\" still has employees assigned and can't be deleted.", type: 'error');

            return;
        }

        $designation->delete();

        $this->dispatch('toast', message: "Designation \"{$designation->name}\" deleted.", type: 'success');
    }

    public function render()
    {
        return view('livewire.designations.index', [
            'designations' => Designation::with('department')->withCount('employees')->orderBy('name')->paginate(15),
        ]);
    }
}
