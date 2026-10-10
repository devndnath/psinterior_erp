<?php

namespace App\Livewire\Vendors;

use App\Models\Vendor;
use App\Services\VendorService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Vendor $vendor = null;

    public string $company_name = '';
    public string $contact_person = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public string $category = '';
    public string $tax_info = '';
    public string $notes = '';

    public function mount(?Vendor $vendor = null): void
    {
        $this->vendor = $vendor?->exists ? $vendor : null;

        Gate::authorize($this->vendor ? 'update' : 'create', $this->vendor ?? Vendor::class);

        if ($this->vendor) {
            $this->fill([
                'company_name' => (string) $this->vendor->company_name,
                'contact_person' => (string) $this->vendor->contact_person,
                'phone' => (string) $this->vendor->phone,
                'email' => (string) $this->vendor->email,
                'address' => (string) $this->vendor->address,
                'category' => (string) $this->vendor->category,
                'tax_info' => (string) $this->vendor->tax_info,
                'notes' => (string) $this->vendor->notes,
            ]);
        }
    }

    protected function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:255'],
            'tax_info' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function save(VendorService $service): void
    {
        $data = $this->validate();

        foreach (['contact_person', 'phone', 'email', 'address', 'category', 'tax_info', 'notes'] as $field) {
            $data[$field] = $data[$field] ?: null;
        }

        if ($this->vendor) {
            $vendor = $service->update($this->vendor, $data);
            $message = "Vendor {$vendor->vendor_code} updated.";
        } else {
            $vendor = $service->create($data);
            $message = "Vendor {$vendor->vendor_code} created.";
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('vendors.show', ['vendor' => $vendor->id], navigate: true);
    }

    #[Title('Vendor')]
    public function render()
    {
        return view('livewire.vendors.form');
    }
}
