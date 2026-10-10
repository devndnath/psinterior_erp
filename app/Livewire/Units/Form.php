<?php

namespace App\Livewire\Units;

use App\Models\Unit;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Unit $unit = null;

    public string $name = '';
    public string $short_code = '';

    public function mount(?Unit $unit = null): void
    {
        $this->unit = $unit?->exists ? $unit : null;

        Gate::authorize($this->unit ? 'update' : 'create', $this->unit ?? Unit::class);

        if ($this->unit) {
            $this->fill([
                'name' => $this->unit->name,
                'short_code' => $this->unit->short_code,
            ]);
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:units,name,'.($this->unit?->id ?: 'NULL')],
            'short_code' => ['required', 'string', 'max:20', 'unique:units,short_code,'.($this->unit?->id ?: 'NULL')],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->unit) {
            $this->unit->update($data);
            $message = 'Unit updated.';
        } else {
            Unit::create($data);
            $message = 'Unit created.';
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('units.index', navigate: true);
    }

    #[Title('Unit')]
    public function render()
    {
        return view('livewire.units.form');
    }
}
