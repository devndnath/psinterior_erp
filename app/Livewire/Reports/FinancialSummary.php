<?php

namespace App\Livewire\Reports;

use App\Enums\InvoiceStatus;
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

        return [
            'total_invoiced' => $totalInvoiced,
            'total_received' => $totalReceived,
            'total_outstanding' => $totalInvoiced - $totalReceived,
            'total_expenses' => $totalExpenses,
        ];
    }

    /**
     * Buckets every currently-unpaid invoice by days past its due date.
     * PHP-side over this bounded set — never a HAVING on an
     * aggregate alias without GROUP BY (the MySQL ONLY_FULL_GROUP_BY
     * pitfall this phase's brief calls out explicitly).
     */
    protected function ageingBuckets(): array
    {
        $buckets = ['0-30' => 0.0, '31-60' => 0.0, '61-90' => 0.0, '90+' => 0.0];

        Invoice::query()
            ->whereIn('status', [InvoiceStatus::Issued->value, InvoiceStatus::PartiallyPaid->value, InvoiceStatus::Overdue->value])
            ->whereNotNull('due_date')
            ->get()
            ->each(function (Invoice $invoice) use (&$buckets) {
                // Carbon's diffInDays() is absolute (always >= 0); the
                // sign ("past due" vs "not yet due") is applied
                // explicitly here rather than relying on a
                // signed-diff convention, to avoid getting it backwards.
                $dueDate = $invoice->due_date->copy()->startOfDay();
                $today = now()->startOfDay();
                $daysPastDue = $dueDate->isPast()
                    ? $today->diffInDays($dueDate)
                    : -1 * $dueDate->diffInDays($today);
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
