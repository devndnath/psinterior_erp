<?php

namespace Database\Factories;

use App\Enums\SnagStatus;
use App\Models\Project;
use App\Models\Snag;
use Illuminate\Database\Eloquent\Factories\Factory;

class SnagFactory extends Factory
{
    protected $model = Snag::class;

    public function definition(): array
    {
        return [
            'snag_no' => 'SNAG-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'project_id' => Project::factory(),
            'area' => $this->faker->randomElement(['Living Room', 'Kitchen', 'Bedroom', 'Bathroom']),
            'issue' => $this->faker->sentence(),
            'status' => SnagStatus::Pending->value,
        ];
    }
}
