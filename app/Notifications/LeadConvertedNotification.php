<?php

namespace App\Notifications;

use App\Models\Client;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Database-channel only (Phase 11 scope — no email sending/config).
 * Fired from LeadService::convertToClient() for the lead's own
 * `assignee`, if one is set.
 */
class LeadConvertedNotification extends Notification
{
    use Queueable;

    public function __construct(protected Lead $lead, protected Client $client) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Lead {$this->lead->lead_no} ({$this->lead->name}) has been converted to client {$this->client->name}.",
            'url' => route('clients.show', $this->client),
        ];
    }
}
