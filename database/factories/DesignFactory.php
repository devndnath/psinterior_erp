<?php

namespace Database\Factories;

use App\Enums\DesignCategory;
use App\Enums\DesignStatus;
use App\Models\Design;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class DesignFactory extends Factory
{
    protected $model = Design::class;

    public function definition(): array
    {
        return [
            'design_no' => 'DSG-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'project_id' => Project::factory(),
            'title' => $this->faker->randomElement(['Living Room Layout', 'Master Bedroom 3D', 'Kitchen Elevation', 'Lobby Rendering']),
            'category' => $this->faker->randomElement(DesignCategory::cases())->value,
            'description' => $this->faker->optional()->sentence(),
            'status' => DesignStatus::Draft->value,
        ];
    }
}
