<?php

namespace Database\Factories;

use App\Models\SelectionBoard;
use App\Models\SelectionOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class SelectionOptionFactory extends Factory
{
    protected $model = SelectionOption::class;

    public function definition(): array
    {
        return [
            'selection_board_id' => SelectionBoard::factory(),
            'label' => $this->faker->randomElement(['Option A', 'Option B', 'Option C']),
            'price' => $this->faker->randomFloat(2, 500, 5000),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
