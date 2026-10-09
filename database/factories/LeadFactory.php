<?php

namespace Database\Factories;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Enums\ProjectType;
use App\Models\Lead;
use App\Models\LeadSource;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'lead_no' => 'LEAD-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'company_name' => $this->faker->boolean(30) ? $this->faker->company() : null,
            'address' => $this->faker->address(),
            'project_type' => $this->faker->randomElement(ProjectType::cases())->value,
            'estimated_budget' => $this->faker->randomFloat(2, 50000, 5000000),
            'interested_service' => $this->faker->randomElement(['Full Interior', 'Kitchen', 'Office Fit-out', 'Renovation']),
            'lead_source_id' => LeadSource::factory(),
            'priority' => $this->faker->randomElement(LeadPriority::cases())->value,
            'status' => LeadStatus::New->value,
            'expected_closing_date' => $this->faker->dateTimeBetween('now', '+2 months'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function won(): static
    {
        return $this->state(fn () => ['status' => LeadStatus::Won->value]);
    }

    public function lost(): static
    {
        return $this->state(fn () => ['status' => LeadStatus::Lost->value]);
    }
}
