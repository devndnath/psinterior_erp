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

    /**
     * A full browser redirect (no `navigate: true`) to a plain GET route,
     * not a direct PDF response — see
     * App\Http\Controllers\Pdf\InvoicePdfController's docblock: a binary
     * PDF can't safely travel through Livewire's own `/livewire/update`
     * JSON response, which is what the old `return Pdf::loadView(...)
     * ->download(...)` here was actually doing under the hood, and why
     * the download button threw "Malformed UTF-8 characters, possibly
     * incorrectly encoded" even after every data-corruption fix.
     */
    public function downloadPdf()
    {
        Gate::authorize('view', $this->invoice);

        return $this->redirect(route('invoices.pdf', $this->invoice));
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
