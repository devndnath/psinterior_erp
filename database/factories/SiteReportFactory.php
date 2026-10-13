<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\SiteReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiteReportFactory extends Factory
{
    protected $model = SiteReport::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'report_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'weather' => $this->faker->randomElement(['Sunny', 'Overcast', 'Rainy', 'Humid']),
            'manpower_count' => $this->faker->numberBetween(2, 40),
            'work_summary' => $this->faker->sentence(12),
            'materials_notes' => $this->faker->optional()->sentence(),
            'issues' => $this->faker->optional()->sentence(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
