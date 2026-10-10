<?php

namespace Database\Factories;

use App\Enums\VendorQuotationStatus;
use App\Models\PurchaseRequest;
use App\Models\Vendor;
use App\Models\VendorQuotation;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorQuotationFactory extends Factory
{
    protected $model = VendorQuotation::class;

    public function definition(): array
    {
        return [
            'purchase_request_id' => PurchaseRequest::factory(),
            'vendor_id' => Vendor::factory(),
            'status' => VendorQuotationStatus::Received->value,
            'total_amount' => $this->faker->randomFloat(2, 1000, 100000),
        ];
    }
}
