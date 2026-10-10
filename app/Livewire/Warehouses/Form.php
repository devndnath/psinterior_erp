<?php

namespace App\Livewire\Warehouses;

use App\Enums\WarehouseType;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Warehouse $warehouse = null;

    public string $name = '';
    public string $type = 'main';
    public string $location = '';

    public function mount(?Warehouse $warehouse = null): void
    {
        $this->warehouse = $warehouse?->exists ? $warehouse : null;

        Gate::authorize($this->warehouse ? 'update' : 'create', $this->warehouse ?? Warehouse::class);

        if ($this->warehouse) {
            $this->fill([
                'name' => $this->warehouse->name,
                'type' => $this->warehouse->type->value,
                'location' => (string) $this->warehouse->location,
            ]);
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:'.implode(',', array_column(WarehouseType::cases(), 'value'))],
            'location' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['location'] = $data['location'] ?: null;

        if ($this->warehouse) {
            $this->warehouse->update($data);
            $message = "Warehouse {$this->warehouse->name} updated.";
        } else {
            $this->warehouse = Warehouse::create($data);
            $message = "Warehouse {$this->warehouse->name} created.";
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('warehouses.show', ['warehouse' => $this->warehouse->id], navigate: true);
    }

    #[Title('Warehouse')]
    public function render()
    {
        return view('livewire.warehouses.form', [
            'types' => WarehouseType::options(),
        ]);
    }
}
