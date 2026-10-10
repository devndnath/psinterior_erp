<?php

namespace App\Livewire\Leaves;

use App\Enums\LeaveType;
use App\Models\Employee;
use App\Models\Leave;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Leaves'])]
#[Title('Leaves')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $employee = '';

    #[Url]
    public string $type = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Leave::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['employee', 'type'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['employee', 'type']);
    }

    public function render()
    {
        $leaves = Leave::query()
            ->with('employee')
            ->when($this->employee, fn ($q) => $q->where('employee_id', $this->employee))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->latest()
            ->paginate(15);

        return view('livewire.leaves.index', [
            'leaves' => $leaves,
            'employees' => Employee::orderBy('name')->get(['id', 'name', 'employee_id']),
            'types' => LeaveType::options(),
        ]);
    }
}
