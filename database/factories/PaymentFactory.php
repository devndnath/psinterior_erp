<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'payment_no' => 'PAY-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'client_id' => Client::factory(),
            'project_id' => Project::factory(),
            'date' => now()->toDateString(),
            'amount' => $this->faker->randomFloat(2, 5000, 200000),
            'method' => PaymentMethod::Bank->value,
        ];
    }
}
