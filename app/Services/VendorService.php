<?php

namespace App\Services;

use App\Models\Vendor;

/**
 * Owns the Vendor aggregate. Deliberately simple CRUD, same shape as
 * ExpenseService — a vendor has no workflow status and no side effects
 * onto any other aggregate.
 */
class VendorService
{
    public function __construct(protected NumberingService $numbering) {}

    public function create(array $data): Vendor
    {
        return Vendor::create([
            'vendor_code' => $this->numbering->next('VEN'),
            'company_name' => $data['company_name'],
            'contact_person' => $data['contact_person'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'category' => $data['category'] ?? null,
            'bank_details' => $data['bank_details'] ?? null,
            'tax_info' => $data['tax_info'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function update(Vendor $vendor, array $data): Vendor
    {
        $vendor->update([
            'company_name' => $data['company_name'] ?? $vendor->company_name,
            'contact_person' => $data['contact_person'] ?? $vendor->contact_person,
            'phone' => $data['phone'] ?? $vendor->phone,
            'email' => $data['email'] ?? $vendor->email,
            'address' => $data['address'] ?? $vendor->address,
            'category' => $data['category'] ?? $vendor->category,
            'bank_details' => $data['bank_details'] ?? $vendor->bank_details,
            'tax_info' => $data['tax_info'] ?? $vendor->tax_info,
            'notes' => $data['notes'] ?? $vendor->notes,
        ]);

        return $vendor->fresh();
    }
}
