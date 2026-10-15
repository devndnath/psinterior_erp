<?php

namespace App\Livewire\Payroll;

use App\Models\Employee;
use App\Models\Payroll;
use App\Services\PayrollService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Payroll $payroll = null;

    public string $employee_id = '';
    public string $month = '';
    public string $basic = '0';
    public string $allowance = '0';
    public string $conveyance = '0';
    public string $overtime = '0';
    public string $bonus = '0';
    public string $deduction = '0';
    public string $advance = '0';

    public function mount(?Payroll $payroll = null): void
    {
        $this->payroll = $payroll?->exists ? $payroll : null;

        Gate::authorize($this->payroll ? 'update' : 'create', $this->payroll ?? Payroll::class);

        if ($this->payroll) {
            $this->fill([
                'employee_id' => (string) $this->payroll->employee_id,
                'month' => $this->payroll->month,
                'basic' => (string) $this->payroll->basic,
                'allowance' => (string) $this->payroll->allowance,
                'conveyance' => (string) $this->payroll->conveyance,
                'overtime' => (string) $this->payroll->overtime,
                'bonus' => (string) $this->payroll->bonus,
                'deduction' => (string) $this->payroll->deduction,
                'advance' => (string) $this->payroll->advance,
            ]);
        } else {
            $this->month = now()->format('Y-m');

            $employeeId = request()->query('employee');

            if ($employeeId && Employee::whereKey($employeeId)->exists()) {
                $this->employee_id = (string) $employeeId;

                $employee = Employee::find($employeeId);
                $this->basic = (string) $employee->salary;
            }
        }
    }

    /**
     * Live-computed net salary shown on the form before saving — the
     * authoritative figure is still recalculated server-side by
     * PayrollService::generate() on save, this is just a preview.
     */
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

        try {
            $payroll = $service->generate($employee, $data['month'], $data);
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');

            return;
        }

        $this->dispatch('toast', message: 'Payroll saved.', type: 'success');
        $this->redirectRoute('payroll.show', ['payroll' => $payroll->id], navigate: true);
    }

    #[Title('Payroll')]
    public function render()
    {
        return view('livewire.payroll.form', [
            'employees' => Employee::orderBy('name')->get(['id', 'name', 'employee_id', 'salary']),
        ]);
    }
}
