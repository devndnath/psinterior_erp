<?php

namespace App\Livewire\Client\Invoices;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Ownership check, not a staff Policy — see "Portal authorization
 * model" in PHASE11_SUMMARY.md. Read-only. Reuses the same
 * resources/views/pdf/invoice.blade.php the staff Show page uses.
 */
#[Layout('layouts.client-portal')]
class Show extends Component
{
    public Invoice $invoice;

    public function mount(Invoice $invoice): void
    {
        $client = Auth::guard('client')->user()->client;
        abort_unless($client, 403, 'No client record linked to this account.');
        abort_unless($invoice->client_id === $client->id, 403);

        $this->invoice = $invoice;
    }

    public function downloadPdf()
    {
        $this->invoice->load(['project', 'client', 'items']);

        return Pdf::loadView('pdf.invoice', ['invoice' => $this->invoice])
            ->download("{$this->invoice->invoice_no}.pdf");
    }

    #[Title('Invoice')]
    public function render()
    {
        $this->invoice->load(['project', 'items', 'payments']);

        return view('livewire.client.invoices.show');
    }
}
