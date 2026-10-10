<?php

namespace Database\Factories;

use App\Enums\MeetingType;
use App\Models\Meeting;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class MeetingFactory extends Factory
{
    protected $model = Meeting::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'type' => $this->faker->randomElement(array_column(MeetingType::cases(), 'value')),
            'date' => now()->toDateString(),
            'location' => $this->faker->city(),
            'agenda' => $this->faker->sentence(),
        ];
    }
}
