<?php

namespace Database\Factories;

use App\Enums\ContractStatus;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractFactory extends Factory
{
    protected $model = Contract::class;

    public function definition(): array
    {
        return [
            'contract_no' => 'CON-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'client_id' => Client::factory(),
            'project_id' => Project::factory(),
            'amount' => $this->faker->randomFloat(2, 100000, 5000000),
            'status' => ContractStatus::Draft->value,
        ];
    }
}
