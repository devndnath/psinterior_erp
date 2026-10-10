<?php

namespace App\Services;

use App\Enums\ServiceRequestStatus;
use App\Models\ServiceRequest;
use App\Models\Warranty;

/**
 * Owns the Service Request status chain: Open -> TechnicianAssigned ->
 * SiteVisit -> Repair -> Verification -> Closed. See
 * ServiceRequestPolicy for why this whole module reuses the
 * `*_warranties` permissions.
 */
class ServiceRequestService
{
    public function __construct(protected NumberingService $numbering) {}

    public function create(Warranty $warranty, array $data): ServiceRequest
    {
        return $warranty->serviceRequests()->create([
            'sr_no' => $this->numbering->next('SR'),
            'client_id' => $data['client_id'],
            'complaint' => $data['complaint'],
            'status' => ServiceRequestStatus::Open->value,
        ]);
    }

    public function assignTechnician(ServiceRequest $sr, int $userId): ServiceRequest
    {
        $this->assertStatus($sr, ServiceRequestStatus::Open);

        $sr->update(['assigned_technician' => $userId, 'status' => ServiceRequestStatus::TechnicianAssigned->value]);

        return $sr->fresh();
    }

    public function scheduleSiteVisit(ServiceRequest $sr): ServiceRequest
    {
        $this->assertStatus($sr, ServiceRequestStatus::TechnicianAssigned);

        $sr->update(['status' => ServiceRequestStatus::SiteVisit->value]);

        return $sr->fresh();
    }

    public function markRepair(ServiceRequest $sr): ServiceRequest
    {
        $this->assertStatus($sr, ServiceRequestStatus::SiteVisit);

        $sr->update(['status' => ServiceRequestStatus::Repair->value]);

        return $sr->fresh();
    }

    public function markVerification(ServiceRequest $sr): ServiceRequest
    {
        $this->assertStatus($sr, ServiceRequestStatus::Repair);

        $sr->update(['status' => ServiceRequestStatus::Verification->value]);

        return $sr->fresh();
    }

    public function close(ServiceRequest $sr): ServiceRequest
    {
        $this->assertStatus($sr, ServiceRequestStatus::Verification);

        $sr->update(['status' => ServiceRequestStatus::Closed->value]);

        return $sr->fresh();
    }

    protected function assertStatus(ServiceRequest $sr, ServiceRequestStatus $expected): void
    {
        if ($sr->status !== $expected) {
            throw new \RuntimeException("This service request must be {$expected->label()} for this action.");
        }
    }
}
