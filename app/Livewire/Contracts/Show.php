<?php

namespace App\Livewire\Contracts;

use App\Models\Contract;
use App\Services\ContractService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Contract $contract;

    public function mount(Contract $contract): void
    {
        Gate::authorize('view', $contract);

        $this->contract = $contract;
    }

    public function activate(ContractService $service): void
    {
        Gate::authorize('update', $this->contract);

        $this->contract = $service->activate($this->contract);

        $this->dispatch('toast', message: "Contract {$this->contract->contract_no} is now Active — project contract value updated.", type: 'success');
    }

    public function downloadPdf()
    {
        Gate::authorize('view', $this->contract);

        $this->contract->load(['project', 'client', 'items']);

        return Pdf::loadView('pdf.contract', ['contract' => $this->contract])
            ->download("{$this->contract->contract_no}.pdf");
    }

    public function deleteContract(): void
    {
        Gate::authorize('delete', $this->contract);

        $no = $this->contract->contract_no;
        $this->contract->delete();

        $this->dispatch('toast', message: "Contract {$no} deleted.", type: 'success');
        $this->redirectRoute('contracts.index', navigate: true);
    }

    #[Title('Contract')]
    public function render()
    {
        $this->contract->load(['project', 'client', 'quotation', 'items']);

        return view('livewire.contracts.show');
    }
}
