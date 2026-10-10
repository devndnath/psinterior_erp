<?php

namespace App\Livewire\Payments;

use App\Enums\PaymentMethod;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Payments'])]
#[Title('Payments')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $method = '';

    #[Url]
    public string $project = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Payment::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['search', 'method', 'project'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'method', 'project']);
    }

    public function deletePayment(int $paymentId): void
    {
        $payment = Payment::findOrFail($paymentId);
        Gate::authorize('delete', $payment);

        app(\App\Services\PaymentService::class)->delete($payment);

        $this->dispatch('toast', message: "Payment {$payment->payment_no} deleted.", type: 'success');
    }

    public function render()
    {
        $payments = Payment::query()
            ->with(['project', 'client', 'invoice'])
            ->when($this->search, fn ($q) => $q->where('payment_no', 'like', "%{$this->search}%"))
            ->when($this->method, fn ($q) => $q->where('method', $this->method))
            ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
            ->latest('date')
            ->paginate(15);

        return view('livewire.payments.index', [
            'payments' => $payments,
            'methods' => PaymentMethod::options(),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
