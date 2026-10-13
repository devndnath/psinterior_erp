<?php

namespace App\Livewire\Vendor\Invoices;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Services\FileService;
use App\Services\VendorInvoiceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * A vendor files an invoice against one of their OWN purchase orders —
 * ownership check, not a staff Policy, same "Portal authorization
 * model" as every other vendor/client screen (PHASE11_SUMMARY.md).
 * Submission is one-shot: there is no edit afterwards (same "filed,
 * then reviewed" shape as a VendorQuotation once selected/rejected) —
 * a mistaken submission is corrected by the vendor filing a fresh one
 * and staff rejecting the wrong one on review.
 */
#[Layout('layouts.vendor-portal')]
class Form extends Component
{
    use WithFileUploads;

    public string $purchase_order_id = '';

    public string $vendor_invoice_no = '';

    public string $invoice_date = '';

    public string $amount = '';

    public string $notes = '';

    /** @var \Illuminate\Http\UploadedFile[] */
    public array $files = [];

    public function mount(): void
    {
        abort_unless(Auth::guard('vendor')->user()->vendor, 403, 'No vendor record linked to this account.');

        $this->invoice_date = now()->toDateString();

        $poId = request()->query('purchase_order');

        if ($poId) {
            $this->purchase_order_id = (string) $poId;
        }
    }

    protected function rules(): array
    {
        return [
            'purchase_order_id' => ['required', 'exists:purchase_orders,id'],
            'vendor_invoice_no' => ['nullable', 'string', 'max:100'],
            'invoice_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'files.*' => ['nullable', 'file', 'max:10240'],
        ];
    }

    public function save(VendorInvoiceService $service, FileService $fileService): void
    {
        $vendor = Auth::guard('vendor')->user()->vendor;
        abort_unless($vendor, 403, 'No vendor record linked to this account.');

        $data = $this->validate();

        $po = PurchaseOrder::findOrFail($data['purchase_order_id']);

        try {
            $invoice = $service->submit($po, $vendor, [
                'vendor_invoice_no' => $data['vendor_invoice_no'] ?: null,
                'invoice_date' => $data['invoice_date'],
                'amount' => $data['amount'],
                'notes' => $data['notes'] ?: null,
            ]);
        } catch (ValidationException $e) {
            $this->addError('purchase_order_id', collect($e->errors())->flatten()->first() ?? 'Could not submit this invoice.');

            return;
        }

        foreach ($this->files as $file) {
            $fileService->store($file, $invoice, 'vendor_invoice_file');
        }

        $this->dispatch('toast', message: "Invoice {$invoice->vi_no} submitted for review.", type: 'success');
        $this->redirectRoute('vendor.invoices.show', ['vendorInvoice' => $invoice->id], navigate: true);
    }

    #[Title('Submit Invoice')]
    public function render()
    {
        $vendor = Auth::guard('vendor')->user()->vendor;

        return view('livewire.vendor.invoices.form', [
            'purchaseOrders' => PurchaseOrder::where('vendor_id', $vendor->id)
                ->whereNotIn('status', [PurchaseOrderStatus::Draft->value, PurchaseOrderStatus::Cancelled->value])
                ->orderByDesc('date')
                ->get(['id', 'po_no', 'total']),
        ]);
    }
}
