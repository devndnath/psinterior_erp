<?php

namespace Database\Factories;

use App\Enums\InspectionResult;
use App\Models\Inspection;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class InspectionFactory extends Factory
{
    protected $model = Inspection::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'area' => $this->faker->randomElement(['Living Room', 'Kitchen', 'Bedroom', 'Bathroom']),
            'inspection_date' => now()->toDateString(),
            'result' => InspectionResult::Passed->value,
        ];
    }
}
