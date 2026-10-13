<?php

namespace Database\Factories;

use App\Enums\SelectionBoardStatus;
use App\Models\Project;
use App\Models\SelectionBoard;
use Illuminate\Database\Eloquent\Factories\Factory;

class SelectionBoardFactory extends Factory
{
    protected $model = SelectionBoard::class;

    public function definition(): array
    {
        return [
            'board_no' => 'SEL-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'project_id' => Project::factory(),
            'area' => $this->faker->randomElement(['Master Bath', 'Kitchen', 'Living Room', null]),
            'title' => $this->faker->randomElement(['Floor Tile', 'Wall Paint', 'Cabinet Hardware', 'Countertop']),
            'description' => $this->faker->optional()->sentence(),
            'status' => SelectionBoardStatus::Draft->value,
        ];
    }
}
