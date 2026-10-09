<?php

namespace Database\Factories;

use App\Enums\QuotationStatus;
use App\Models\Client;
use App\Models\Project;
use App\Models\Quotation;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuotationFactory extends Factory
{
    protected $model = Quotation::class;

    public function definition(): array
    {
        return [
            'quotation_no' => 'QUO-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'client_id' => Client::factory(),
            'project_id' => Project::factory(),
            'version' => 1,
            'status' => QuotationStatus::Draft->value,
            'subtotal' => 0,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 0,
        ];
    }
}
