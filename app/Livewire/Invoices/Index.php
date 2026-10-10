<?php

namespace App\Livewire\Invoices;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Invoices'])]
#[Title('Invoices')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $type = '';

    #[Url]
    public string $project = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Invoice::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['search', 'status', 'type', 'project'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'type', 'project']);
    }

    public function deleteInvoice(int $invoiceId): void
    {
        $invoice = Invoice::findOrFail($invoiceId);
        Gate::authorize('delete', $invoice);

        $invoice->delete();

        $this->dispatch('toast', message: "Invoice {$invoice->invoice_no} deleted.", type: 'success');
    }

    public function render()
    {
        $invoices = Invoice::query()
            ->with(['project', 'client'])
            ->when($this->search, fn ($q) => $q->where('invoice_no', 'like', "%{$this->search}%"))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
            ->latest()
            ->paginate(15);

        return view('livewire.invoices.index', [
            'invoices' => $invoices,
            'statuses' => InvoiceStatus::options(),
            'types' => InvoiceType::options(),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
