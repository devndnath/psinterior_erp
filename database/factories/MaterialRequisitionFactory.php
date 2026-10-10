<?php

namespace Database\Factories;

use App\Enums\MaterialRequisitionStatus;
use App\Models\MaterialRequisition;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialRequisitionFactory extends Factory
{
    protected $model = MaterialRequisition::class;

    public function definition(): array
    {
        return [
            'mr_no' => 'MR-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'project_id' => Project::factory(),
            'date' => now()->toDateString(),
            'purpose' => $this->faker->sentence(),
            'status' => MaterialRequisitionStatus::Pending->value,
        ];
    }
}
