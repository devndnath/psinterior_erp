<?php

namespace App\Services;

use App\Models\SubcontractorAssignment;
use App\Models\SubcontractorPayment;

/**
 * Plain CRUD — no invoice/status side effects, mirroring PaymentService
 * for the parts of it that don't touch an Invoice (this payment has no
 * client-facing document to reconcile against, only the assignment's
 * running totalPaid()).
 */
class SubcontractorPaymentService
{
    public function create(SubcontractorAssignment $assignment, array $data): SubcontractorPayment
    {
        return $assignment->payments()->create([
            'date' => $data['date'],
            'amount' => $data['amount'],
            'notes' => $data['notes'] ?? null,
        ]);
    }
}
