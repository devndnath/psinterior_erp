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

    /**
     * See App\Http\Controllers\Pdf\InvoicePdfController's docblock — same
     * fix, same reason: a binary PDF can't safely travel through
     * Livewire's own JSON response, so this redirects (full browser
     * navigation, not `navigate: true`) to a plain GET route instead.
     * The blade's "Print" button links straight to the same route with
     * `?print=1` (see Pdf\Concerns\RespondsWithPdf) and doesn't need a
     * Livewire action at all, since it opens in a new tab.
     */
    public function downloadPdf()
    {
        Gate::authorize('view', $this->payment);

        return $this->redirect(route('payments.pdf', $this->payment));
    }

    #[Title('Payment')]
    public function render()
    {
        $this->payment->load(['project', 'client', 'invoice']);

        return view('livewire.payments.show');
    }
}
