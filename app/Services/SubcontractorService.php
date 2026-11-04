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
            'name' => array_key_exists('name', $data) ? $data['name'] : $subcontractor->name,
            'trade' => array_key_exists('trade', $data) ? $data['trade'] : $subcontractor->trade->value,
            'phone' => array_key_exists('phone', $data) ? $data['phone'] : $subcontractor->phone,
            'address' => array_key_exists('address', $data) ? $data['address'] : $subcontractor->address,
            'rate_type' => array_key_exists('rate_type', $data) ? $data['rate_type'] : $subcontractor->rate_type,
        ]);

        return $subcontractor->fresh();
    }
}
