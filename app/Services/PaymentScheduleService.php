<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\PaymentScheduleLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Owns the payment-schedule-line sub-resource of a Contract — closes the
 * "no structured payment plan" audit gap. Gated through ContractPolicy's
 * existing view/update abilities (same as ContractItem), not a new
 * permission module: a schedule line only ever exists under one
 * contract, the same way a line item does.
 */
class PaymentScheduleService
{
    protected const AMOUNT_TOLERANCE = 0.01;

    public function addLine(Contract $contract, array $data): PaymentScheduleLine
    {
        $amount = $this->resolveAmount($contract, $data);

        $this->assertWithinContractAmount($contract, $amount);

        return DB::transaction(function () use ($contract, $data, $amount) {
            $nextSort = (int) $contract->scheduleLines()->max('sort_order') + 1;

            return $contract->scheduleLines()->create([
                'milestone_id' => $data['milestone_id'] ?? null,
                'label' => $data['label'],
                'percentage' => $data['percentage'] ?? null,
                'amount' => $amount,
                'due_date' => $data['due_date'] ?? null,
                'sort_order' => $data['sort_order'] ?? $nextSort,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    public function updateLine(PaymentScheduleLine $line, array $data): PaymentScheduleLine
    {
        $contract = $line->contract;
        $amount = $this->resolveAmount($contract, $data);

        $this->assertWithinContractAmount($contract, $amount, excludingLineId: $line->id);

        $line->update([
            'milestone_id' => $data['milestone_id'] ?? null,
            'label' => $data['label'],
            'percentage' => $data['percentage'] ?? null,
            'amount' => $amount,
            'due_date' => $data['due_date'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return $line->fresh();
    }

    public function removeLine(PaymentScheduleLine $line): void
    {
        if ($line->isInvoiced()) {
            throw ValidationException::withMessages([
                'line' => 'This installment is already linked to an invoice — unlink it first if it needs to be removed.',
            ]);
        }

        $line->delete();
    }

    /**
     * Tags an installment as "this is the invoice raised for it" —
     * deliberately a loose, after-the-fact link rather than the service
     * creating the invoice itself. Staff may invoice slightly early,
     * combine installments, or invoice via a route this service doesn't
     * need to know about; this just records which invoice answered which
     * planned line.
     */
    public function linkInvoice(PaymentScheduleLine $line, Invoice $invoice): PaymentScheduleLine
    {
        if ($invoice->contract_id !== $line->contract_id) {
            throw ValidationException::withMessages([
                'invoice' => 'That invoice does not belong to this contract.',
            ]);
        }

        $line->update(['invoice_id' => $invoice->id]);

        return $line->fresh();
    }

    public function unlinkInvoice(PaymentScheduleLine $line): PaymentScheduleLine
    {
        $line->update(['invoice_id' => null]);

        return $line->fresh();
    }

    /**
     * The actual answer to the audit's example: "we were supposed to
     * collect $15,000 by now but have only invoiced $9,000." Everything
     * here reads off the schedule lines and whatever invoice each is
     * linked to — nothing is stored redundantly, so it can never drift
     * from the underlying Invoice/Payment data.
     */
    public function summary(Contract $contract): array
    {
        $lines = $contract->scheduleLines()->with('invoice')->get();
        $today = now()->toDateString();

        $totalScheduled = (float) $lines->sum('amount');
        $dueByToday = (float) $lines->filter(fn (PaymentScheduleLine $l) => $l->due_date !== null && $l->due_date->toDateString() <= $today)->sum('amount');
        $invoicedSoFar = (float) $lines->filter(fn (PaymentScheduleLine $l) => $l->isInvoiced())->sum('amount');
        $collectedSoFar = (float) $lines->filter(fn (PaymentScheduleLine $l) => $l->isInvoiced())->sum(fn (PaymentScheduleLine $l) => (float) $l->invoice->paid_amount);

        return [
            'total_scheduled' => $totalScheduled,
            'remaining_to_schedule' => (float) $contract->amount - $totalScheduled,
            'due_by_today' => $dueByToday,
            'invoiced_so_far' => $invoicedSoFar,
            'collected_so_far' => $collectedSoFar,
            'outstanding_invoiced' => $invoicedSoFar - $collectedSoFar,
            // Negative = behind schedule (owed more than has been
            // invoiced by now); positive = ahead of schedule.
            'variance_due_vs_invoiced' => $invoicedSoFar - $dueByToday,
        ];
    }

    protected function resolveAmount(Contract $contract, array $data): float
    {
        if (isset($data['amount']) && $data['amount'] !== '' && $data['amount'] !== null) {
            return round((float) $data['amount'], 2);
        }

        if (! empty($data['percentage'])) {
            return round((float) $contract->amount * (float) $data['percentage'] / 100, 2);
        }

        throw ValidationException::withMessages([
            'amount' => 'Enter either a fixed amount or a percentage of the contract value.',
        ]);
    }

    protected function assertWithinContractAmount(Contract $contract, float $newAmount, ?int $excludingLineId = null): void
    {
        $existingTotal = (float) $contract->scheduleLines()
            ->when($excludingLineId, fn ($q) => $q->where('id', '!=', $excludingLineId))
            ->sum('amount');

        if ($existingTotal + $newAmount > (float) $contract->amount + self::AMOUNT_TOLERANCE) {
            throw ValidationException::withMessages([
                'amount' => sprintf(
                    'This would schedule %s against a contract worth %s — reduce this line or another one first.',
                    number_format($existingTotal + $newAmount, 2),
                    number_format((float) $contract->amount, 2),
                ),
            ]);
        }
    }
}
