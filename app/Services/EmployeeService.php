<?php

namespace App\Services;

use App\Enums\EmployeeStatus;
use App\Models\Employee;

/**
 * Owns the Employee aggregate. `create()` assigns the `employee_id`
 * business code via NumberingService::next('EMP') — see Employee's
 * class docblock and the employees migration for why this column name
 * is NOT a foreign key despite the same name appearing as a real FK on
 * Attendance/Leave/Payroll.
 */
class EmployeeService
{
    public function __construct(protected NumberingService $numbering) {}

    public function create(array $data): Employee
    {
        return Employee::create([
            'employee_id' => $this->numbering->next('EMP'),
            'user_id' => $data['user_id'] ?? null,
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'designation_id' => $data['designation_id'] ?? null,
            'joining_date' => $data['joining_date'],
            'salary' => $data['salary'] ?? 0,
            'employment_type' => $data['employment_type'],
            'emergency_contact' => $data['emergency_contact'] ?? null,
            'status' => $data['status'] ?? EmployeeStatus::Active->value,
        ]);
    }

    public function update(Employee $employee, array $data): Employee
    {
        $employee->update([
            'user_id' => array_key_exists('user_id', $data) ? $data['user_id'] : $employee->user_id,
            'name' => array_key_exists('name', $data) ? $data['name'] : $employee->name,
            'phone' => array_key_exists('phone', $data) ? $data['phone'] : $employee->phone,
            'email' => array_key_exists('email', $data) ? $data['email'] : $employee->email,
            'address' => array_key_exists('address', $data) ? $data['address'] : $employee->address,
            'department_id' => array_key_exists('department_id', $data) ? $data['department_id'] : $employee->department_id,
            'designation_id' => array_key_exists('designation_id', $data) ? $data['designation_id'] : $employee->designation_id,
            'joining_date' => array_key_exists('joining_date', $data) ? $data['joining_date'] : $employee->joining_date,
            'salary' => array_key_exists('salary', $data) ? $data['salary'] : $employee->salary,
            'employment_type' => array_key_exists('employment_type', $data) ? $data['employment_type'] : $employee->employment_type->value,
            'emergency_contact' => array_key_exists('emergency_contact', $data) ? $data['emergency_contact'] : $employee->emergency_contact,
            'status' => array_key_exists('status', $data) ? $data['status'] : $employee->status->value,
        ]);

        return $employee->fresh();
    }
}
