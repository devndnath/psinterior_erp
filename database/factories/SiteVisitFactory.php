<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\SiteVisit;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiteVisitFactory extends Factory
{
    protected $model = SiteVisit::class;

    public function definition(): array
    {
        return [
            'sv_no' => 'SV-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'client_id' => Client::factory(),
            'site_address' => $this->faker->address(),
            'visit_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'purpose' => $this->faker->randomElement(['Measurement', 'Initial assessment', 'Follow-up']),
            'requirements' => $this->faker->optional()->sentence(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
