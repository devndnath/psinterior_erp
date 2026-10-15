<?php

namespace App\Livewire\Payroll;

use App\Models\Employee;
use App\Models\Payroll;
use App\Services\PayrollService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * "Generate Payroll" — an alternative, smarter entry point to the same
 * Draft Payroll the plain manual Form creates, pre-filled from real data
 * instead of typed by hand:
 *
 *  - Basic: the employee's configured salary.
 *  - Overtime: computed from Attendance check-in/check-out pairs for the
 *    month (hours beyond 8/day, at 1.5x the derived hourly rate).
 *  - Conveyance: the sum of the employee's Approved ExpenseClaim rows
 *    for the month that haven't already been attached to a payroll.
 *  - Deduction: unpaid-absence days x the derived per-day rate.
 *
 * Bonus/advance have no data source to compute from, so they stay
 * manual inputs. Every computed figure is still just a pre-filled,
 * editable suggestion — nothing is saved until Save is pressed, and
 * PayrollService::generate() remains the single place a Payroll row is
 * actually written, so this page can never bypass its Draft-only /
 * already-finalized guard. Gated the same as the manual Form
 * (`create_payroll`) — this is a different way to fill the form, not a
 * different ability.
 */
#[Layout('layouts.admin', ['title' => 'Generate Payroll'])]
#[Title('Generate Payroll')]
class Generate extends Component
{
    public string $employee_id = '';
    public string $month = '';

    public string $basic = '0';
    public string $allowance = '0';
    public string $conveyance = '0';
    public string $overtime = '0';
    public string $bonus = '0';
    public string $deduction = '0';
    public string $advance = '0';

    /** @var int[] ExpenseClaim ids computed into the current `conveyance` figure. */
    public array $claimIds = [];

    public ?array $summary = null;

    public function mount(): void
    {
        Gate::authorize('create', Payroll::class);

        $this->month = now()->format('Y-m');
    }

    public function updatedEmployeeId(): void
    {
        $this->compute();
    }

    public function updatedMonth(): void
    {
        $this->compute();
    }

    public function compute(PayrollService $service): void
    {
        $this->summary = null;
        $this->claimIds = [];

        if (! $this->employee_id || ! preg_match('/^\d{4}-\d{2}$/', $this->month)) {
            return;
        }

        $employee = Employee::find($this->employee_id);

        if (! $employee) {
            return;
        }

        $existing = Payroll::where('employee_id', $employee->id)->where('month', $this->month)->first();

        if ($existing && $existing->status->value !== 'draft') {
            $this->dispatch('toast', message: "Payroll for {$employee->name} / {$this->month} is already {$existing->status->label()} — edit it directly instead.", type: 'error');

            return;
        }

        $draft = $service->computeDraft($employee, $this->month);

        $this->basic = (string) $draft['basic'];
        $this->overtime = (string) $draft['overtime'];
        $this->conveyance = (string) $draft['conveyance'];
        $this->deduction = (string) $draft['deduction'];
        $this->claimIds = $draft['claim_ids'];
        $this->summary = $draft['summary'];

        if ($existing) {
            // A draft already exists for this month — carry over whatever
            // HR had already typed for the manual-only fields rather than
            // silently resetting them back to 0.
            $this->allowance = (string) $existing->allowance;
            $this->bonus = (string) $existing->bonus;
            $this->advance = (string) $existing->advance;
        }
    }

    public function getNetSalaryProperty(): float
    {
        return (float) $this->basic + (float) $this->allowance + (float) $this->conveyance + (float) $this->overtime
            + (float) $this->bonus - (float) $this->deduction - (float) $this->advance;
    }

    protected function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'month' => ['required', 'date_format:Y-m'],
            'basic' => ['required', 'numeric', 'min:0'],
            'allowance' => ['required', 'numeric', 'min:0'],
            'conveyance' => ['required', 'numeric', 'min:0'],
            'overtime' => ['required', 'numeric', 'min:0'],
            'bonus' => ['required', 'numeric', 'min:0'],
            'deduction' => ['required', 'numeric', 'min:0'],
            'advance' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function save(PayrollService $service): void
    {
        $data = $this->validate();

        $employee = Employee::findOrFail($data['employee_id']);
        $data['claim_ids'] = $this->claimIds;

        try {
            $payroll = $service->generate($employee, $data['month'], $data);
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');

            return;
        }

        $this->dispatch('toast', message: 'Payroll generated.', type: 'success');
        $this->redirectRoute('payroll.show', ['payroll' => $payroll->id], navigate: true);
    }

    public function render()
    {
        return view('livewire.payroll.generate', [
            'employees' => Employee::orderBy('name')->get(['id', 'name', 'employee_id', 'salary']),
        ]);
    }
}
