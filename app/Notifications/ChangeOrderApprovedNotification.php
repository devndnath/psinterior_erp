<?php

namespace App\Notifications;

use App\Models\ChangeOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Database-channel only. Fired from
 * ChangeOrderService::recordClientDecision() when the decision is
 * Approved, for the project's project manager.
 */
class ChangeOrderApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(protected ChangeOrder $changeOrder) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Change Order {$this->changeOrder->co_no} has been approved by the client.",
            'url' => route('change-orders.show', $this->changeOrder),
        ];
    }
}
