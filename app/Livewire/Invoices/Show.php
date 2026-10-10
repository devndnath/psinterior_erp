<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Invoice $invoice;

    public function mount(Invoice $invoice): void
    {
        Gate::authorize('view', $invoice);

        $this->invoice = $invoice;
    }

    public function issueInvoice(InvoiceService $service): void
    {
        Gate::authorize('issue', $this->invoice);

        $this->invoice = $service->issue($this->invoice);

        $this->dispatch('toast', message: "Invoice {$this->invoice->invoice_no} issued.", type: 'success');
    }

    public function cancelInvoice(InvoiceService $service): void
    {
        Gate::authorize('cancel', $this->invoice);

        try {
            $this->invoice = $service->cancel($this->invoice);
            $this->dispatch('toast', message: "Invoice {$this->invoice->invoice_no} cancelled.", type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function deleteInvoice(): void
    {
        Gate::authorize('delete', $this->invoice);

        $no = $this->invoice->invoice_no;
        $this->invoice->delete();

        $this->dispatch('toast', message: "Invoice {$no} deleted.", type: 'success');
        $this->redirectRoute('invoices.index', navigate: true);
    }

    #[Title('Invoice')]
    public function render()
    {
        $this->invoice->load(['project', 'client', 'contract', 'items', 'payments']);

        return view('livewire.invoices.show');
    }
}
