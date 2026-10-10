<?php

namespace App\Livewire\Payments;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Payment $payment;

    public function mount(Payment $payment): void
    {
        Gate::authorize('view', $payment);

        $this->payment = $payment;
    }

    public function deletePayment(PaymentService $service): void
    {
        Gate::authorize('delete', $this->payment);

        $no = $this->payment->payment_no;
        $service->delete($this->payment);

        $this->dispatch('toast', message: "Payment {$no} deleted.", type: 'success');
        $this->redirectRoute('payments.index', navigate: true);
    }

    #[Title('Payment')]
    public function render()
    {
        $this->payment->load(['project', 'client', 'invoice']);

        return view('livewire.payments.show');
    }
}
