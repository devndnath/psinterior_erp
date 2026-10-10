<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 10000, 500000);

        return [
            'invoice_no' => 'INV-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'client_id' => Client::factory(),
            'project_id' => Project::factory(),
            'type' => InvoiceType::Progress->value,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'subtotal' => $subtotal,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => $subtotal,
            'paid_amount' => 0,
            'status' => InvoiceStatus::Draft->value,
        ];
    }
}
