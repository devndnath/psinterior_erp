<?php

namespace Database\Factories;

use App\Enums\PayrollStatus;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollFactory extends Factory
{
    protected $model = Payroll::class;

    public function definition(): array
    {
        $basic = $this->faker->randomFloat(2, 15000, 80000);

        return [
            'employee_id' => Employee::factory(),
            'month' => now()->format('Y-m'),
            'basic' => $basic,
            'allowance' => 0,
            'overtime' => 0,
            'bonus' => 0,
            'deduction' => 0,
            'advance' => 0,
            'net_salary' => $basic,
            'status' => PayrollStatus::Draft->value,
        ];
    }
}
