<?php

namespace App\Livewire\Expenses;

use App\Enums\PaymentMethod;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\Vendor;
use App\Services\ExpenseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Expense $expense = null;

    public string $project_id = '';
    public string $category_id = '';
    public string $vendor_name = '';
    public string $vendor_id = '';
    public string $amount = '';
    public string $date = '';
    public string $method = 'cash';
    public string $description = '';

    public function mount(?Expense $expense = null): void
    {
        $this->expense = $expense?->exists ? $expense : null;

        Gate::authorize($this->expense ? 'update' : 'create', $this->expense ?? Expense::class);

        if ($this->expense) {
            $this->fill([
                'project_id' => (string) $this->expense->project_id,
                'category_id' => (string) $this->expense->category_id,
                'vendor_name' => (string) $this->expense->vendor_name,
                'vendor_id' => (string) $this->expense->vendor_id,
                'amount' => (string) $this->expense->amount,
                'date' => $this->expense->date?->format('Y-m-d') ?? '',
                'method' => $this->expense->method->value,
                'description' => (string) $this->expense->description,
            ]);

            return;
        }

        $this->date = now()->toDateString();

        $projectId = request()->query('project');

        if ($projectId && Project::whereKey($projectId)->exists()) {
            $this->project_id = (string) $projectId;
        }
    }

    protected function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'category_id' => ['required', 'exists:expense_categories,id'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'method' => ['required', 'string', 'in:'.implode(',', array_column(PaymentMethod::cases(), 'value'))],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function save(ExpenseService $service): void
    {
        $data = $this->validate();
        $data['vendor_name'] = $data['vendor_name'] ?: null;
        $data['vendor_id'] = $data['vendor_id'] ?: null;
        $data['description'] = $data['description'] ?: null;

        if ($this->expense) {
            $expense = $service->update($this->expense, $data);
            $message = "Expense {$expense->expense_no} updated.";
        } else {
            $data['created_by'] = Auth::id();
            $expense = $service->create($data);
            $message = "Expense {$expense->expense_no} created.";
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('expenses.show', ['expense' => $expense->id], navigate: true);
    }

    #[Title('Expense')]
    public function render()
    {
        return view('livewire.expenses.form', [
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
            'categories' => ExpenseCategory::active()->orderBy('name')->get(),
            'vendors' => Vendor::orderBy('company_name')->get(['id', 'company_name']),
            'methods' => PaymentMethod::options(),
        ]);
    }
}
