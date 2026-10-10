<?php

namespace App\Models;

use App\Enums\EmployeeStatus;
use App\Enums\EmploymentType;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * NOTE on `employee_id`: this model's `employee_id` attribute is a plain
 * STRING business code (e.g. "EMP-000001", issued by
 * EmployeeService via NumberingService::next('EMP')) — it is NOT a
 * foreign key and is unrelated to this model's own `id` primary key.
 * Every related table (Attendance, Leave, Payroll) also has a column
 * literally called `employee_id`, but on those tables it IS a real
 * foreign key pointing back at `employees.id` (this model's PK), never
 * at this string column. See the migration comments on both sides for
 * the full explanation — don't assume the two are the same kind of
 * column just because they share a name.
 */
class Employee extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'employee_id', 'user_id', 'name', 'phone', 'email', 'address',
        'department_id', 'designation_id', 'joining_date', 'salary',
        'employment_type', 'emergency_contact', 'status',
    ];

    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
            'salary' => 'decimal:2',
            'employment_type' => EmploymentType::class,
            'status' => EmployeeStatus::class,
        ];
    }

    public function logAttributes(): array
    {
        return ['status', 'salary'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function latestPayroll(): ?Payroll
    {
        return $this->payrolls()->latest('month')->first();
    }
}
