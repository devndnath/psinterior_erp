<?php

namespace App\Livewire\Subcontractors;

use App\Enums\SubcontractorTrade;
use App\Models\Subcontractor;
use App\Services\SubcontractorService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Subcontractor $subcontractor = null;

    public string $name = '';
    public string $trade = 'carpenter';
    public string $phone = '';
    public string $address = '';
    public string $rate_type = '';

    public function mount(?Subcontractor $subcontractor = null): void
    {
        $this->subcontractor = $subcontractor?->exists ? $subcontractor : null;

        Gate::authorize($this->subcontractor ? 'update' : 'create', $this->subcontractor ?? Subcontractor::class);

        if ($this->subcontractor) {
            $this->fill([
                'name' => $this->subcontractor->name,
                'trade' => $this->subcontractor->trade->value,
                'phone' => (string) $this->subcontractor->phone,
                'address' => (string) $this->subcontractor->address,
                'rate_type' => (string) $this->subcontractor->rate_type,
            ]);
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'trade' => ['required', 'string', 'in:'.implode(',', array_column(SubcontractorTrade::cases(), 'value'))],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:2000'],
            'rate_type' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function save(SubcontractorService $service): void
    {
        $data = $this->validate();

        if ($this->subcontractor) {
            $subcontractor = $service->update($this->subcontractor, $data);
            $message = "Subcontractor \"{$subcontractor->name}\" updated.";
        } else {
            $subcontractor = $service->create($data);
            $message = "Subcontractor \"{$subcontractor->name}\" created.";
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('subcontractors.show', ['subcontractor' => $subcontractor->id], navigate: true);
    }

    #[Title('Subcontractor')]
    public function render()
    {
        return view('livewire.subcontractors.form', [
            'trades' => SubcontractorTrade::options(),
        ]);
    }
}
