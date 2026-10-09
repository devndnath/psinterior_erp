<?php

namespace Database\Factories;

use App\Enums\LeadPriority;
use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'project_code' => 'PRJ-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'name' => $this->faker->catchPhrase().' Interior Fit-out',
            'client_id' => Client::factory(),
            'type' => $this->faker->randomElement(ProjectType::cases())->value,
            'location' => $this->faker->city(),
            'start_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'expected_end_date' => $this->faker->dateTimeBetween('+2 months', '+6 months'),
            'contract_value' => $this->faker->randomFloat(2, 100000, 10000000),
            'estimated_budget' => $this->faker->randomFloat(2, 80000, 9000000),
            'priority' => $this->faker->randomElement(LeadPriority::cases())->value,
            'status' => ProjectStatus::Planning->value,
            'progress' => 0,
            'description' => $this->faker->optional()->paragraph(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => ProjectStatus::Completed->value, 'progress' => 100]);
    }
}
