<?php

namespace Database\Factories;

use App\Enums\DesignRevisionStatus;
use App\Models\Design;
use App\Models\DesignRevision;
use Illuminate\Database\Eloquent\Factories\Factory;

class DesignRevisionFactory extends Factory
{
    protected $model = DesignRevision::class;

    public function definition(): array
    {
        return [
            'design_id' => Design::factory(),
            'version' => 1,
            'description' => $this->faker->optional()->sentence(),
            'status' => DesignRevisionStatus::Submitted->value,
        ];
    }
}
