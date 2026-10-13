<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\PaymentScheduleLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentScheduleLineFactory extends Factory
{
    protected $model = PaymentScheduleLine::class;

    public function definition(): array
    {
        return [
            'contract_id' => Contract::factory(),
            'label' => $this->faker->randomElement(['Advance', 'Design Approval', 'Material Delivery', 'Handover']),
            'amount' => $this->faker->randomFloat(2, 10000, 100000),
            'due_date' => $this->faker->dateTimeBetween('-1 month', '+2 months'),
            'sort_order' => 0,
        ];
    }
}
