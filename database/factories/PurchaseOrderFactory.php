<?php

namespace Database\Factories;

use App\Enums\PurchaseOrderStatus;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'po_no' => 'PO-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'vendor_id' => Vendor::factory(),
            'project_id' => Project::factory(),
            'warehouse_id' => Warehouse::factory(),
            'date' => now()->toDateString(),
            'status' => PurchaseOrderStatus::Draft->value,
        ];
    }
}
