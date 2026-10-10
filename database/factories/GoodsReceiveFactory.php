<?php

namespace Database\Factories;

use App\Models\GoodsReceive;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoodsReceiveFactory extends Factory
{
    protected $model = GoodsReceive::class;

    public function definition(): array
    {
        return [
            'grn_no' => 'GRN-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'purchase_order_id' => PurchaseOrder::factory(),
            'received_date' => now()->toDateString(),
        ];
    }
}
