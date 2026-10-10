<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'expense_no' => 'EXP-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'project_id' => Project::factory(),
            'category_id' => ExpenseCategory::factory(),
            'vendor_name' => $this->faker->company(),
            'amount' => $this->faker->randomFloat(2, 1000, 100000),
            'date' => now()->toDateString(),
            'method' => PaymentMethod::Cash->value,
        ];
    }
}
