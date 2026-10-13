<?php

namespace App\Livewire\Departments;

use App\Models\Department;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Closes the "Department management" audit gap — the Department model
 * and its migration have existed since Phase 1 and Employee already
 * references department_id, but until now the only way to create one
 * was a raw DB insert. Same shape as ExpenseCategories/ProductCategories
 * (Index + Form, no Show page), gated on `manage_employees` instead of
 * `manage_settings` — see DepartmentPolicy's docblock.
 */
#[Layout('layouts.admin', ['title' => 'Departments'])]
#[Title('Departments')]
class Index extends Component
{
    use WithPagination;

    public function mount(): void
    {
        Gate::authorize('viewAny', Department::class);
    }

    public function deleteDepartment(int $departmentId): void
    {
        $department = Department::findOrFail($departmentId);
        Gate::authorize('delete', $department);

        if ($department->employees()->exists() || $department->designations()->exists()) {
            $this->dispatch('toast', message: "\"{$department->name}\" still has employees or designations attached and can't be deleted.", type: 'error');

            return;
        }

        $department->delete();

        $this->dispatch('toast', message: "Department \"{$department->name}\" deleted.", type: 'success');
    }

    public function render()
    {
        return view('livewire.departments.index', [
            'departments' => Department::withCount(['employees', 'designations'])->orderBy('name')->paginate(15),
        ]);
    }
}
