<?php

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;
use App\Notifications\InvoiceOverdueNotification;
use Illuminate\Console\Command;

/**
 * Closes a known issue from Phase 6's summary: nothing previously
 * flipped a past-due Issued/PartiallyPaid invoice to Overdue. Scheduled
 * daily in routes/console.php. `invoices.status` is a plain string
 * column (App\Enums\InvoiceStatus), so no migration is needed to add
 * the Overdue value — it already exists in the enum and was already
 * usable, just never auto-set.
 */
class FlagOverdueInvoices extends Command
{
    protected $signature = 'invoices:flag-overdue';

    protected $description = 'Flags every Issued/PartiallyPaid invoice whose due date has passed as Overdue, and notifies the relevant staff.';

    public function handle(): int
    {
        $invoices = Invoice::query()
            ->whereIn('status', [InvoiceStatus::Issued->value, InvoiceStatus::PartiallyPaid->value])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->with('project.projectManager')
            ->get();

        if ($invoices->isEmpty()) {
            $this->info('No overdue invoices found.');

            return self::SUCCESS;
        }

        $financeUsers = User::permission('view_finance')->get();

        foreach ($invoices as $invoice) {
            $invoice->update(['status' => InvoiceStatus::Overdue->value]);

            $recipients = $financeUsers->concat([$invoice->project?->projectManager])
                ->filter()
                ->unique('id');

            foreach ($recipients as $recipient) {
                $recipient->notify(new InvoiceOverdueNotification($invoice));
            }
        }

        $this->info("Flagged {$invoices->count()} invoice(s) as overdue.");

        return self::SUCCESS;
    }
}
