<?php

namespace App\Livewire\Reports;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Total invoiced/received/outstanding + expenses across a date range,
 * plus an invoice ageing breakdown. The ageing buckets are computed in
 * PHP over the (small, bounded — only unpaid invoices) result set
 * rather than a fragile SQL HAVING clause, the same reasoning as the
 * low-stock WHERE-subquery fix in Products/Index.
 */
#[Layout('layouts.admin', ['title' => 'Financial Summary Report'])]
#[Title('Financial Summary Report')]
class FinancialSummary extends Component
{
    public function mount(): void
    {
        // Also gated by route middleware; checked here too so the
        // component can't be mounted without the ability.
        abort_unless(auth()->user()?->can('view_reports'), 403);
    }

    public string $dateFrom = '';
    public string $dateTo = '';

    protected function invoicesQuery()
    {
        return Invoice::query()
            ->where('status', '!=', InvoiceStatus::Cancelled->value)
            ->when($this->dateFrom, fn ($q) => $q->whereDate('invoice_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('invoice_date', '<=', $this->dateTo));
    }

    protected function totals(): array
    {
        $totalInvoiced = (float) $this->invoicesQuery()->sum('grand_total');
        $totalReceived = (float) Payment::query()
            ->when($this->dateFrom, fn ($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('date', '<=', $this->dateTo))
            ->sum('amount');
        $totalExpenses = (float) Expense::query()
            ->when($this->dateFrom, fn ($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('date', '<=', $this->dateTo))
            ->sum('amount');

        // Design income: anything billed as a Design Sale, plus money
        // received with no project at all (a design sold on its own).
        $designInvoiced = (float) $this->invoicesQuery()
            ->where('type', InvoiceType::Design->value)
            ->sum('grand_total');
        $designReceived = (float) Payment::query()
            ->when($this->dateFrom, fn ($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('date', '<=', $this->dateTo))
            ->where(fn ($q) => $q->whereNull('project_id')
                ->orWhereHas('invoice', fn ($i) => $i->where('type', InvoiceType::Design->value)))
            ->sum('amount');

        return [
            'design_invoiced' => $designInvoiced,
            'design_received' => $designReceived,
            'project_invoiced' => $totalInvoiced - $designInvoiced,
            'project_received' => $totalReceived - $designReceived,
            'total_invoiced' => $totalInvoiced,
            'total_received' => $totalReceived,
            'total_outstanding' => $totalInvoiced - $totalReceived,
            'total_expenses' => $totalExpenses,
        ];
    }

    /**
     * Buckets every currently-unpaid invoice (within the selected date
     * range) by days past its due date. PHP-side over this bounded set —
     * never a HAVING on an aggregate alias without GROUP BY (the MySQL
     * ONLY_FULL_GROUP_BY pitfall this phase's brief calls out explicitly).
     *
     * Phase 12 hardening: this used to ignore dateFrom/dateTo entirely
     * and pull EVERY unpaid invoice the company has ever issued — the
     * same filter totals() already applies is now enforced here too, so
     * the bucket query stays bounded by the chosen range (and the
     * report is internally consistent) instead of growing unbounded
     * with the company's full invoice history. Only the columns the
     * bucketing loop actually needs are selected to keep each row small.
     */
    protected function ageingBuckets(): array
    {
        $buckets = ['0-30' => 0.0, '31-60' => 0.0, '61-90' => 0.0, '90+' => 0.0];

        Invoice::query()
            ->select(['id', 'due_date', 'grand_total', 'paid_amount'])
            ->whereIn('status', [InvoiceStatus::Issued->value, InvoiceStatus::PartiallyPaid->value, InvoiceStatus::Overdue->value])
            ->whereNotNull('due_date')
            ->when($this->dateFrom, fn ($q) => $q->whereDate('invoice_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('invoice_date', '<=', $this->dateTo))
            ->get()
            ->each(function (Invoice $invoice) use (&$buckets) {
                // Carbon 3's diffInDays() is signed: due->diffInDays(today)
                // is positive once the due date has passed, negative
                // before it. (The old code assumed Carbon 2's absolute
                // value, which put every overdue invoice in 0-30.)
                $daysPastDue = (int) $invoice->due_date->copy()->startOfDay()
                    ->diffInDays(now()->startOfDay());
                $outstanding = $invoice->outstandingAmount();

                if ($outstanding <= 0) {
                    return;
                }

                $bucket = match (true) {
                    $daysPastDue <= 30 => '0-30',
                    $daysPastDue <= 60 => '31-60',
                    $daysPastDue <= 90 => '61-90',
                    default => '90+',
                };

                $buckets[$bucket] += $outstanding;
            });

        return $buckets;
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $totals = $this->totals();
        $ageing = $this->ageingBuckets();

        return response()->streamDownload(function () use ($totals, $ageing) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Metric', 'Amount']);
            fputcsv($out, ['Total Invoiced', $totals['total_invoiced']]);
            fputcsv($out, ['Total Received', $totals['total_received']]);
            fputcsv($out, ['Total Outstanding', $totals['total_outstanding']]);
            fputcsv($out, ['Total Expenses', $totals['total_expenses']]);
            fputcsv($out, []);
            fputcsv($out, ['Income by source', 'Invoiced', 'Received']);
            fputcsv($out, ['Project work', $totals['project_invoiced'], $totals['project_received']]);
            fputcsv($out, ['Design sales', $totals['design_invoiced'], $totals['design_received']]);
            fputcsv($out, []);
            fputcsv($out, ['Ageing Bucket (days overdue)', 'Outstanding Amount']);
            foreach ($ageing as $bucket => $amount) {
                fputcsv($out, [$bucket, $amount]);
            }
            fclose($out);
        }, 'financial-summary-report.csv');
    }

    public function render()
    {
        return view('livewire.reports.financial-summary', [
            'totals' => $this->totals(),
            'ageing' => $this->ageingBuckets(),
        ]);
    }
}
