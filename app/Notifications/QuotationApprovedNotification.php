<?php

namespace App\Notifications;

use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Database-channel only. Fired from QuotationService::changeStatus()
 * when a quotation moves to Approved, for the project's
 * `project_manager_id` user, if one is set.
 */
class QuotationApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(protected Quotation $quotation) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Quotation {$this->quotation->quotation_no} has been approved by the client.",
            'url' => route('quotations.show', $this->quotation),
        ];
    }
}
