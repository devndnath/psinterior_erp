<?php

namespace Database\Factories;

use App\Enums\LeaveApprovalStatus;
use App\Enums\LeaveType;
use App\Models\Employee;
use App\Models\Leave;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveFactory extends Factory
{
    protected $model = Leave::class;

    public function definition(): array
    {
        $from = $this->faker->dateTimeBetween('now', '+1 month');

        return [
            'employee_id' => Employee::factory(),
            'type' => LeaveType::Casual->value,
            'from_date' => $from->format('Y-m-d'),
            'to_date' => $from->format('Y-m-d'),
            'reason' => $this->faker->sentence(),
            'manager_status' => LeaveApprovalStatus::Pending->value,
            'hr_status' => LeaveApprovalStatus::Pending->value,
        ];
    }
}
