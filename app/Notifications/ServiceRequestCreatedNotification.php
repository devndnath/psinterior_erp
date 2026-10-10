<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Database-channel only. Fired from ServiceRequestService::create().
 *
 * There is no single "warranty desk" role in this codebase, so the
 * recipient is every staff user holding `edit_warranties` — the same
 * permission ServiceRequestPolicy already gates every status
 * transition on (see its class docblock) — rather than inventing a new
 * recipient concept just for this one notification.
 */
class ServiceRequestCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(protected ServiceRequest $serviceRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "New Service Request {$this->serviceRequest->sr_no}: {$this->serviceRequest->complaint}",
            'url' => route('service-requests.show', $this->serviceRequest),
        ];
    }
}
