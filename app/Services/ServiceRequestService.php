<?php

namespace App\Services;

use App\Enums\ServiceRequestStatus;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Warranty;
use App\Notifications\ServiceRequestCreatedNotification;

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
        $serviceRequest = $warranty->serviceRequests()->create([
            'sr_no' => $this->numbering->next('SR'),
            'client_id' => $data['client_id'],
            'complaint' => $data['complaint'],
            'status' => ServiceRequestStatus::Open->value,
        ]);

        // Database-channel notification only (Phase 11). There is no
        // single "warranty desk" role/recipient in this codebase, so
        // every user holding `edit_warranties` — the same permission
        // ServiceRequestPolicy already gates every status transition
        // on — is notified, rather than inventing a new recipient
        // concept.
        User::permission('edit_warranties')->get()->each(
            fn (User $user) => $user->notify(new ServiceRequestCreatedNotification($serviceRequest))
        );

        return $serviceRequest;
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
