<?php

namespace Database\Factories;

use App\Enums\LeadPriority;
use App\Enums\MilestoneStatus;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectMilestoneFactory extends Factory
{
    protected $model = ProjectMilestone::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => $this->faker->randomElement(['Design Approval', 'Material Procurement', 'Civil Work', 'Furniture Installation', 'Final Handover']),
            'description' => $this->faker->optional()->sentence(),
            'start_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'due_date' => $this->faker->dateTimeBetween('now', '+2 months'),
            'status' => MilestoneStatus::Pending->value,
            'progress' => 0,
            'priority' => $this->faker->randomElement(LeadPriority::cases())->value,
        ];
    }
}
