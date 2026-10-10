<?php

namespace App\Services;

use App\Enums\PayrollStatus;
use App\Models\Employee;
use App\Models\Payroll;

/**
 * Owns the Payroll aggregate. `generate()` is safe to call repeatedly
 * for the same employee+month while the payroll is still Draft (it's an
 * updateOrCreate), but refuses outright once it has left Draft —
 * mirroring InvoiceService::update()'s "a paid invoice is never edited"
 * refusal — so a Finalized/Paid payroll's figures can never be silently
 * recomputed out from under an already-issued payslip.
 */
class PayrollService
{
    public function generate(Employee $employee, string $month, array $data): Payroll
    {
        $existing = Payroll::where('employee_id', $employee->id)->where('month', $month)->first();

        if ($existing && $existing->status !== PayrollStatus::Draft) {
            throw new \RuntimeException("Payroll for {$employee->name} / {$month} is already {$existing->status->label()} and cannot be regenerated.");
        }

        $basic = (float) ($data['basic'] ?? 0);
        $allowance = (float) ($data['allowance'] ?? 0);
        $overtime = (float) ($data['overtime'] ?? 0);
        $bonus = (float) ($data['bonus'] ?? 0);
        $deduction = (float) ($data['deduction'] ?? 0);
        $advance = (float) ($data['advance'] ?? 0);

        $netSalary = $basic + $allowance + $overtime + $bonus - $deduction - $advance;

        return Payroll::updateOrCreate(
            ['employee_id' => $employee->id, 'month' => $month],
            [
                'basic' => $basic,
                'allowance' => $allowance,
                'overtime' => $overtime,
                'bonus' => $bonus,
                'deduction' => $deduction,
                'advance' => $advance,
                'net_salary' => $netSalary,
                'status' => PayrollStatus::Draft->value,
            ]
        );
    }

    public function finalize(Payroll $payroll): Payroll
    {
        if ($payroll->status !== PayrollStatus::Draft) {
            throw new \RuntimeException('Only a draft payroll can be finalized.');
        }

        $payroll->update(['status' => PayrollStatus::Finalized->value]);

        return $payroll->fresh();
    }

    public function markPaid(Payroll $payroll): Payroll
    {
        if ($payroll->status !== PayrollStatus::Finalized) {
            throw new \RuntimeException('Only a finalized payroll can be marked paid.');
        }

        $payroll->update(['status' => PayrollStatus::Paid->value]);

        return $payroll->fresh();
    }
}
