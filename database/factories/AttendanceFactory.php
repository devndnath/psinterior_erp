<?php

namespace Database\Factories;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'date' => $this->faker->unique()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'status' => AttendanceStatus::Present->value,
        ];
    }
}
