<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Subcontractor;
use App\Models\SubcontractorAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubcontractorAssignmentFactory extends Factory
{
    protected $model = SubcontractorAssignment::class;

    public function definition(): array
    {
        return [
            'subcontractor_id' => Subcontractor::factory(),
            'project_id' => Project::factory(),
            'work_description' => $this->faker->sentence(),
            'rate' => $this->faker->randomFloat(2, 5000, 100000),
            'progress' => 0,
        ];
    }
}
