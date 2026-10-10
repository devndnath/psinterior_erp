<?php

namespace Database\Factories;

use App\Enums\WarehouseType;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true).' Warehouse',
            'type' => WarehouseType::Main->value,
            'location' => $this->faker->city(),
        ];
    }
}
