<?php

namespace App\Livewire\Vendors;

use App\Models\Vendor;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Vendors'])]
#[Title('Vendors')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Vendor::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search']);
    }

    public function deleteVendor(int $vendorId): void
    {
        $vendor = Vendor::findOrFail($vendorId);
        Gate::authorize('delete', $vendor);

        $vendor->delete();

        $this->dispatch('toast', message: "Vendor {$vendor->vendor_code} deleted.", type: 'success');
    }

    public function render()
    {
        $vendors = Vendor::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('company_name', 'like', "%{$this->search}%")
                    ->orWhere('vendor_code', 'like', "%{$this->search}%");
            }))
            ->latest()
            ->paginate(15);

        return view('livewire.vendors.index', [
            'vendors' => $vendors,
        ]);
    }
}
