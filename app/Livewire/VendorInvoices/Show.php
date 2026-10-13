<?php

namespace App\Livewire\VendorInvoices;

use App\Enums\PaymentMethod;
use App\Models\ExpenseCategory;
use App\Models\VendorInvoice;
use App\Services\VendorInvoiceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public VendorInvoice $vendorInvoice;

    public bool $showReviewForm = false;

    public string $categoryId = '';

    public string $method = 'bank';

    public string $reviewNotes = '';

    public function mount(VendorInvoice $vendorInvoice): void
    {
        Gate::authorize('view', $vendorInvoice);

        $this->vendorInvoice = $vendorInvoice;
    }

    public function toggleReviewForm(): void
    {
        Gate::authorize('review', $this->vendorInvoice);

        $this->showReviewForm = ! $this->showReviewForm;
    }

    public function approve(VendorInvoiceService $service): void
    {
        Gate::authorize('review', $this->vendorInvoice);

        $data = $this->validate([
            'categoryId' => ['required', 'exists:expense_categories,id'],
            'method' => ['required', 'string'],
            'reviewNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->vendorInvoice = $service->approve($this->vendorInvoice, [
                'category_id' => $data['categoryId'],
                'method' => $data['method'],
                'review_notes' => $data['reviewNotes'] ?: null,
            ], Auth::id());
        } catch (ValidationException $e) {
            $this->dispatch('toast', message: collect($e->errors())->flatten()->first() ?? 'Could not approve this invoice.', type: 'error');

            return;
        }

        $this->showReviewForm = false;
        $this->dispatch('toast', message: "Vendor invoice {$this->vendorInvoice->vi_no} approved and posted to expenses.", type: 'success');
    }

    public function reject(VendorInvoiceService $service): void
    {
        Gate::authorize('review', $this->vendorInvoice);

        $data = $this->validate([
            'reviewNotes' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $this->vendorInvoice = $service->reject($this->vendorInvoice, $data['reviewNotes'], Auth::id());
        } catch (ValidationException $e) {
            $this->dispatch('toast', message: collect($e->errors())->flatten()->first() ?? 'Could not reject this invoice.', type: 'error');

            return;
        }

        $this->showReviewForm = false;
        $this->dispatch('toast', message: "Vendor invoice {$this->vendorInvoice->vi_no} rejected.", type: 'success');
    }

    #[Title('Vendor Invoice')]
    public function render()
    {
        $this->vendorInvoice->load(['vendor', 'purchaseOrder', 'reviewer', 'expense.category']);

        return view('livewire.vendor-invoices.show', [
            'photos' => $this->vendorInvoice->documents,
            'categories' => ExpenseCategory::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'methods' => PaymentMethod::options(),
        ]);
    }
}
