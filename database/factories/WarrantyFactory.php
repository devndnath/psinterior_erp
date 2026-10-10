<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Warranty;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarrantyFactory extends Factory
{
    protected $model = Warranty::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'terms' => $this->faker->sentence(),
        ];
    }
}
