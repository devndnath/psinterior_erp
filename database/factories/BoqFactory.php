<?php

namespace Database\Factories;

use App\Enums\BoqStatus;
use App\Models\Boq;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoqFactory extends Factory
{
    protected $model = Boq::class;

    public function definition(): array
    {
        return [
            'boq_no' => 'BOQ-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'project_id' => Project::factory(),
            'version' => 1,
            'status' => BoqStatus::Draft->value,
            'subtotal' => 0,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 0,
        ];
    }
}
