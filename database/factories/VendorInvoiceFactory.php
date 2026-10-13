<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Models\VendorInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorInvoiceFactory extends Factory
{
    protected $model = VendorInvoice::class;

    public function definition(): array
    {
        return [
            'vi_no' => 'VI-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'purchase_order_id' => PurchaseOrder::factory(),
            'vendor_id' => Vendor::factory(),
            'vendor_invoice_no' => $this->faker->bothify('INV-####'),
            'invoice_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'amount' => $this->faker->randomFloat(2, 1000, 50000),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
