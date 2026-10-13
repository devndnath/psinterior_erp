<?php

namespace App\Livewire\Departments;

use App\Models\Department;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Department $department = null;

    public string $name = '';
    public string $code = '';

    public function mount(?Department $department = null): void
    {
        $this->department = $department?->exists ? $department : null;

        Gate::authorize($this->department ? 'update' : 'create', $this->department ?? Department::class);

        if ($this->department) {
            $this->fill([
                'name' => $this->department->name,
                'code' => (string) $this->department->code,
            ]);
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:departments,name,'.($this->department?->id ?: 'NULL')],
            'code' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['code'] = $data['code'] ?: null;

        if ($this->department) {
            $this->department->update($data);
            $message = 'Department updated.';
        } else {
            Department::create($data);
            $message = 'Department created.';
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('departments.index', navigate: true);
    }

    #[Title('Department')]
    public function render()
    {
        return view('livewire.departments.form');
    }
}
