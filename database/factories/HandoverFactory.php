<?php

namespace Database\Factories;

use App\Models\Handover;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class HandoverFactory extends Factory
{
    protected $model = Handover::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'handover_date' => now()->toDateString(),
            'notes' => $this->faker->sentence(),
        ];
    }
}
