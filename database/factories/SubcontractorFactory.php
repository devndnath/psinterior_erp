<?php

namespace Database\Factories;

use App\Enums\SubcontractorTrade;
use App\Models\Subcontractor;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubcontractorFactory extends Factory
{
    protected $model = Subcontractor::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'trade' => SubcontractorTrade::Carpenter->value,
            'phone' => $this->faker->phoneNumber(),
            'rate_type' => $this->faker->randomElement(['per sqft', 'per day', 'lump sum']),
        ];
    }
}
