<?php

namespace App\Livewire\VendorInvoices;

use App\Enums\VendorInvoiceStatus;
use App\Models\Vendor;
use App\Models\VendorInvoice;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Vendor Invoices'])]
#[Title('Vendor Invoices')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $vendor = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', VendorInvoice::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['search', 'vendor', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'vendor', 'status']);
    }

    public function render()
    {
        $invoices = VendorInvoice::query()
            ->with(['vendor', 'purchaseOrder'])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('vi_no', 'like', "%{$this->search}%")
                ->orWhere('vendor_invoice_no', 'like', "%{$this->search}%")
                ->orWhereHas('vendor', fn ($q) => $q->where('company_name', 'like', "%{$this->search}%"))
            ))
            ->when($this->vendor, fn ($q) => $q->where('vendor_id', $this->vendor))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.vendor-invoices.index', [
            'invoices' => $invoices,
            'vendors' => Vendor::orderBy('company_name')->get(['id', 'company_name']),
            'statusOptions' => VendorInvoiceStatus::options(),
        ]);
    }
}
