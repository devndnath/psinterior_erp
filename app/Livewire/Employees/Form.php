<?php

namespace App\Livewire\Employees;

use App\Enums\EmployeeStatus;
use App\Enums\EmploymentType;
use App\Enums\UserType;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use App\Services\EmployeeService;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Employee $employee = null;

    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public string $department_id = '';
    public string $designation_id = '';
    public string $joining_date = '';
    public string $salary = '0';
    public string $employment_type = 'full_time';
    public string $emergency_contact = '';
    public string $status = 'active';

    /**
     * "Create a login for this employee" — only offered when there is
     * no linked account yet (Employee.user_id), and only acted on if
     * the submitting user also holds `create_users`: creating Employees
     * and creating Users are different privileges (EmployeePolicy vs
     * UserPolicy), and this checkbox is a side door into the second one,
     * so save() re-checks it server-side rather than trusting that the
     * view hid the section — see save()'s Gate::authorize call.
     */
    public bool $create_account = false;

    /** @var array<int,string> */
    public array $account_roles = [];

    public function mount(?Employee $employee = null): void
    {
        $this->employee = $employee?->exists ? $employee : null;

        Gate::authorize($this->employee ? 'update' : 'create', $this->employee ?? Employee::class);

        if ($this->employee) {
            $this->fill([
                'name' => $this->employee->name,
                'phone' => (string) $this->employee->phone,
                'email' => (string) $this->employee->email,
                'address' => (string) $this->employee->address,
                'department_id' => (string) $this->employee->department_id,
                'designation_id' => (string) $this->employee->designation_id,
                'joining_date' => $this->employee->joining_date?->toDateString() ?? '',
                'salary' => (string) $this->employee->salary,
                'employment_type' => $this->employee->employment_type->value,
                'emergency_contact' => (string) $this->employee->emergency_contact,
                'status' => $this->employee->status->value,
            ]);
        } else {
            $this->joining_date = now()->toDateString();
        }
    }

    /**
     * Dependent select: the designation dropdown is filtered to the
     * currently-chosen department. No existing form in this codebase
     * has a dependent-select pair yet, so this is a new pattern — kept
     * simple with a plain helper re-evaluated on render() each time
     * wire:model.live="department_id" changes, the same "plain helper,
     * not #[Computed]" convention Payments/Form.php's
     * availableInvoices() already uses, rather than a dedicated
     * JS/Alpine component.
     */
    protected function designationOptions()
    {
        return Designation::query()
            ->when($this->department_id, fn ($q) => $q->where('department_id', $this->department_id))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function updatedDepartmentId(): void
    {
        if ($this->designation_id && ! Designation::where('id', $this->designation_id)->where('department_id', $this->department_id)->exists()) {
            $this->designation_id = '';
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            // A login needs a real, globally-unique email (`users.email`
            // is unique across all three guards) — the field stays
            // optional for an Employee with no login.
            'email' => $this->create_account
                ? ['required', 'email', 'max:255', 'unique:users,email']
                : ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'joining_date' => ['required', 'date'],
            'salary' => ['required', 'numeric', 'min:0'],
            'employment_type' => ['required', 'string', 'in:'.implode(',', array_column(EmploymentType::cases(), 'value'))],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:'.implode(',', array_column(EmployeeStatus::cases(), 'value'))],
            'account_roles' => ['array'],
            'account_roles.*' => ['string', 'exists:roles,name,guard_name,web'],
        ];
    }

    public function save(EmployeeService $service, UserService $users): void
    {
        $data = $this->validate();

        $data['department_id'] = $data['department_id'] ?: null;
        $data['designation_id'] = $data['designation_id'] ?: null;

        $wantsAccount = $this->create_account && ! $this->employee?->user_id;

        if ($wantsAccount) {
            // Re-checked server-side — the view only shows this
            // checkbox to someone who already holds `create_users`, but
            // a crafted request could still set the property directly.
            Gate::authorize('create', User::class);
        }

        $employee = DB::transaction(function () use ($service, $users, $data, $wantsAccount) {
            $employee = $this->employee
                ? $service->update($this->employee, $data)
                : $service->create($data);

            if ($wantsAccount) {
                // super-admin is deliberately never offered in this
                // inline list (see the Blade view) — stripped again
                // here as defense in depth.
                $roles = array_values(array_diff($this->account_roles, ['super-admin']));

                ['user' => $user, 'password' => $password] = $users->createLinkedAccount(
                    $employee->name,
                    $employee->email,
                    UserType::Staff,
                    $roles,
                );

                $employee->update(['user_id' => $user->id]);

                // No outbound email in this app — handed back once, in
                // the session, so the Employee Show page can display it
                // exactly once right after the redirect below.
                session()->flash('generated_password', $password);
                session()->flash('generated_password_for', $user->email);
            }

            return $employee;
        });

        $message = $this->employee
            ? "Employee {$employee->employee_id} updated."
            : "Employee {$employee->employee_id} created.".($wantsAccount ? ' Login created.' : '');

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('employees.show', ['employee' => $employee->id], navigate: true);
    }

    #[Title('Employee')]
    public function render()
    {
        return view('livewire.employees.form', [
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'designations' => $this->designationOptions(),
            'employmentTypes' => EmploymentType::options(),
            'statuses' => EmployeeStatus::options(),
            'canCreateAccounts' => auth()->user()->can('create_users'),
            'availableRoles' => Role::where('guard_name', 'web')->where('name', '!=', 'super-admin')->orderBy('name')->pluck('name'),
        ]);
    }
}
