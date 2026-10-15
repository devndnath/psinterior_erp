<?php

namespace App\Livewire\MyExpenseClaims;

use App\Enums\ExpenseClaimType;
use App\Models\Employee;
use App\Models\Project;
use App\Models\SiteVisit;
use App\Services\ExpenseClaimService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * "New Expense Claim" self-service form — e.g. conveyance to a site
 * visit or a client meeting. Always submits for the current user's own
 * Employee record. See MyExpenseClaims\Index's docblock for the
 * authorization split from ExpenseClaimPolicy.
 */
#[Layout('layouts.admin')]
class Form extends Component
{
    public Employee $employee;

    public string $type = 'conveyance';
    public string $project_id = '';
    public string $site_visit_id = '';
    public string $date = '';
    public string $amount = '';
    public string $description = '';

    public function mount(): void
    {
        $employee = Auth::user()->employee;

        abort_if(! $employee, 403, 'Your account is not linked to an employee record, so you cannot submit an expense claim.');

        $this->employee = $employee;
        $this->date = now()->toDateString();
    }

    protected function rules(): array
    {
        return [
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

        $service->create($this->employee, $data);

        $this->dispatch('toast', message: 'Expense claim submitted.', type: 'success');
        $this->redirectRoute('my-expense-claims.index', navigate: true);
    }

    #[Title('New Expense Claim')]
    public function render()
    {
        return view('livewire.my-expense-claims.form', [
            'types' => ExpenseClaimType::options(),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
            'siteVisits' => SiteVisit::latest('visit_date')->limit(100)->get(['id', 'sv_no', 'visit_date']),
        ]);
    }
}
