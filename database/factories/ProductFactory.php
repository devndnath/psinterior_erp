<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'sku' => 'SKU-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'name' => $this->faker->words(3, true),
            'category_id' => ProductCategory::factory(),
            'brand' => $this->faker->company(),
            'unit_id' => Unit::factory(),
            'purchase_price' => $this->faker->randomFloat(2, 10, 5000),
            'usage_rate' => $this->faker->randomFloat(2, 1, 50),
            'min_stock' => $this->faker->randomFloat(2, 5, 100),
            'description' => $this->faker->sentence(),
        ];
    }
}
