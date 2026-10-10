<?php

namespace App\Models;

use App\Enums\PayrollStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'month', 'basic', 'allowance', 'overtime', 'bonus',
        'deduction', 'advance', 'net_salary', 'status',
    ];

    protected function casts(): array
    {
        return [
            'basic' => 'decimal:2',
            'allowance' => 'decimal:2',
            'overtime' => 'decimal:2',
            'bonus' => 'decimal:2',
            'deduction' => 'decimal:2',
            'advance' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'status' => PayrollStatus::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
