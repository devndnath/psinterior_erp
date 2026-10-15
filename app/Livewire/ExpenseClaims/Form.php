<?php

namespace App\Livewire\ExpenseClaims;

use App\Enums\ExpenseClaimType;
use App\Models\Employee;
use App\Models\ExpenseClaim;
use App\Models\Project;
use App\Models\SiteVisit;
use App\Services\ExpenseClaimService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Admin-side create/edit for an expense claim — mainly for HR entering a
 * claim on behalf of an employee who doesn't use the self-service screen
 * (e.g. no portal login yet). Mirrors Expenses\Form's shape. Editing is
 * only ever offered while the claim is still Pending (see
 * ExpenseClaimPolicy::update()).
 */
#[Layout('layouts.admin')]
class Form extends Component
{
    public ?ExpenseClaim $claim = null;

    public string $employee_id = '';
    public string $type = 'conveyance';
    public string $project_id = '';
    public string $site_visit_id = '';
    public string $date = '';
    public string $amount = '';
    public string $description = '';

    public function mount(?ExpenseClaim $expenseClaim = null): void
    {
        $this->claim = $expenseClaim?->exists ? $expenseClaim : null;

        Gate::authorize($this->claim ? 'update' : 'create', $this->claim ?? ExpenseClaim::class);

        if ($this->claim) {
            $this->fill([
                'employee_id' => (string) $this->claim->employee_id,
                'type' => $this->claim->type->value,
                'project_id' => (string) $this->claim->project_id,
                'site_visit_id' => (string) $this->claim->site_visit_id,
                'date' => $this->claim->date?->format('Y-m-d') ?? '',
                'amount' => (string) $this->claim->amount,
                'description' => (string) $this->claim->description,
            ]);

            return;
        }

        $this->date = now()->toDateString();

        $employeeId = request()->query('employee');

        if ($employeeId && Employee::whereKey($employeeId)->exists()) {
            $this->employee_id = (string) $employeeId;
        }
    }

    protected function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'type' => ['required', 'string', 'in:'.implode(',', array_column(ExpenseClaimType::cases(), 'value'))],
            'project_id' => ['nullable', 'exists:projects,id'],
            'site_visit_id' => ['nullable', 'exists:site_visits,id'],
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function save(ExpenseClaimService $service): void
    {
        $data = $this->validate();
        $data['project_id'] = $data['project_id'] ?: null;
        $data['site_visit_id'] = $data['site_visit_id'] ?: null;
        $data['description'] = $data['description'] ?: null;

        if ($this->claim) {
            $claim = $service->update($this->claim, $data);
            $message = 'Expense claim updated.';
        } else {
            $employee = Employee::findOrFail($data['employee_id']);
            $claim = $service->create($employee, $data);
            $message = 'Expense claim submitted.';
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('expense-claims.show', ['expenseClaim' => $claim->id], navigate: true);
    }

    #[Title('Expense Claim')]
    public function render()
    {
        return view('livewire.expense-claims.form', [
            'employees' => Employee::orderBy('name')->get(['id', 'name', 'employee_id']),
            'types' => ExpenseClaimType::options(),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
            'siteVisits' => SiteVisit::latest('visit_date')->limit(100)->get(['id', 'sv_no', 'visit_date']),
        ]);
    }
}
