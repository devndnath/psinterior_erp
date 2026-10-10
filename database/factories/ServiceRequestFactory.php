<?php

namespace Database\Factories;

use App\Enums\ServiceRequestStatus;
use App\Models\Client;
use App\Models\ServiceRequest;
use App\Models\Warranty;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceRequestFactory extends Factory
{
    protected $model = ServiceRequest::class;

    public function definition(): array
    {
        return [
            'sr_no' => 'SR-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'warranty_id' => Warranty::factory(),
            'client_id' => Client::factory(),
            'complaint' => $this->faker->sentence(),
            'status' => ServiceRequestStatus::Open->value,
        ];
    }
}
