<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Database-channel only. Fired from the `invoices:flag-overdue` console
 * command for the invoice's project manager and every user holding
 * `view_finance`.
 */
class InvoiceOverdueNotification extends Notification
{
    use Queueable;

    public function __construct(protected Invoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Invoice {$this->invoice->invoice_no} is now overdue (due {$this->invoice->due_date?->format('d M, Y')}).",
            'url' => route('invoices.show', $this->invoice),
        ];
    }
}
