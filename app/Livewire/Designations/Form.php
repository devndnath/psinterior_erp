<?php

namespace App\Livewire\Designations;

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Designation $designation = null;

    public string $department_id = '';
    public string $name = '';

    public function mount(?Designation $designation = null): void
    {
        $this->designation = $designation?->exists ? $designation : null;

        Gate::authorize($this->designation ? 'update' : 'create', $this->designation ?? Designation::class);

        if ($this->designation) {
            $this->fill([
                'department_id' => (string) $this->designation->department_id,
                'name' => $this->designation->name,
            ]);
        }
    }

    protected function rules(): array
    {
        return [
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->designation) {
            $this->designation->update($data);
            $message = 'Designation updated.';
        } else {
            Designation::create($data);
            $message = 'Designation created.';
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('designations.index', navigate: true);
    }

    #[Title('Designation')]
    public function render()
    {
        return view('livewire.designations.form', [
            'departments' => Department::orderBy('name')->get(),
        ]);
    }
}
