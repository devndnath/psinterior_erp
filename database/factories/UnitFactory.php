<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'short_code' => strtoupper($this->faker->unique()->lexify('???')),
        ];
    }
}
