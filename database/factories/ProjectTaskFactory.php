<?php

namespace Database\Factories;

use App\Enums\LeadPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectTaskFactory extends Factory
{
    protected $model = ProjectTask::class;

    public function definition(): array
    {
        return [
            'task_no' => 'TSK-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'project_id' => Project::factory(),
            'name' => $this->faker->sentence(4),
            'description' => $this->faker->optional()->sentence(),
            'start_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'priority' => $this->faker->randomElement(LeadPriority::cases())->value,
            'status' => TaskStatus::Todo->value,
            'progress' => 0,
        ];
    }
}
