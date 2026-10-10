<?php

namespace Database\Factories;

use App\Enums\PurchaseRequestStatus;
use App\Models\Project;
use App\Models\PurchaseRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseRequestFactory extends Factory
{
    protected $model = PurchaseRequest::class;

    public function definition(): array
    {
        return [
            'pr_no' => 'PR-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'project_id' => Project::factory(),
            'status' => PurchaseRequestStatus::Draft->value,
        ];
    }
}
