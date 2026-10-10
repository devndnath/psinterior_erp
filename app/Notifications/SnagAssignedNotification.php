<?php

namespace App\Notifications;

use App\Models\Snag;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Database-channel only. Fired from SnagService::assign() for the
 * newly-assigned user.
 */
class SnagAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(protected Snag $snag) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Snag {$this->snag->snag_no} ({$this->snag->issue}) has been assigned to you.",
            'url' => route('snags.show', $this->snag),
        ];
    }
}
