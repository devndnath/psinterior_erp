<?php

namespace Database\Factories;

use App\Enums\ChangeOrderStatus;
use App\Models\ChangeOrder;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChangeOrderFactory extends Factory
{
    protected $model = ChangeOrder::class;

    public function definition(): array
    {
        return [
            'co_no' => 'CO-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'project_id' => Project::factory(),
            'description' => $this->faker->sentence(),
            'cost' => $this->faker->randomFloat(2, 5000, 200000),
            'status' => ChangeOrderStatus::Pending->value,
        ];
    }
}
