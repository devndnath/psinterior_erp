<?php

namespace Database\Factories;

use App\Enums\EmployeeStatus;
use App\Enums\EmploymentType;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'employee_id' => 'EMP-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'joining_date' => $this->faker->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
            'salary' => $this->faker->randomFloat(2, 15000, 120000),
            'employment_type' => EmploymentType::FullTime->value,
            'status' => EmployeeStatus::Active->value,
        ];
    }
}
