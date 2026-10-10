<?php

namespace App\Services;

use App\Models\Subcontractor;

/**
 * Plain CRUD — subcontractors have no unique business code in the spec,
 * unlike Vendor's `vendor_code`, so no NumberingService dependency here.
 */
class SubcontractorService
{
    public function create(array $data): Subcontractor
    {
        return Subcontractor::create([
            'name' => $data['name'],
            'trade' => $data['trade'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'rate_type' => $data['rate_type'] ?? null,
        ]);
    }

    public function update(Subcontractor $subcontractor, array $data): Subcontractor
    {
        $subcontractor->update([
            'name' => $data['name'] ?? $subcontractor->name,
            'trade' => $data['trade'] ?? $subcontractor->trade->value,
            'phone' => $data['phone'] ?? $subcontractor->phone,
            'address' => $data['address'] ?? $subcontractor->address,
            'rate_type' => $data['rate_type'] ?? $subcontractor->rate_type,
        ]);

        return $subcontractor->fresh();
    }
}
