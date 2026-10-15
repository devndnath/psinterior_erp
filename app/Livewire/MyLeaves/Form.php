<?php

namespace App\Livewire\MyLeaves;

use App\Enums\LeaveType;
use App\Models\Employee;
use App\Services\LeaveService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * "Apply Leave" self-service form — always submits for the current
 * user's own Employee record (no employee picker, unlike the admin-side
 * Leaves\Form). See MyLeaves\Index's docblock for the authorization
 * split from LeavePolicy. Reuses the exact same LeaveService::create()
 * the admin form calls, so a self-submitted request lands in the same
 * manager+HR Pending/Pending approval queue either way.
 */
#[Layout('layouts.admin')]
class Form extends Component
{
    public Employee $employee;

    public string $type = 'casual';
    public string $from_date = '';
    public string $to_date = '';
    public string $reason = '';

    public function mount(): void
    {
        $employee = Auth::user()->employee;

        abort_if(! $employee, 403, 'Your account is not linked to an employee record, so you cannot apply for leave.');

        $this->employee = $employee;
        $this->from_date = now()->toDateString();
        $this->to_date = now()->toDateString();
    }

    protected function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:'.implode(',', array_column(LeaveType::cases(), 'value'))],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function save(LeaveService $service): void
    {
        $data = $this->validate();

        $service->create($this->employee, $data);

        $this->dispatch('toast', message: 'Leave request submitted.', type: 'success');
        $this->redirectRoute('my-leaves.index', navigate: true);
    }

    #[Title('Apply for Leave')]
    public function render()
    {
        return view('livewire.my-leaves.form', [
            'types' => LeaveType::options(),
        ]);
    }
}
