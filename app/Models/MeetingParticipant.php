<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MeetingParticipant extends Model
{
    protected $fillable = ['meeting_id', 'participant_id', 'participant_type'];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function participant(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The participant's display name, regardless of which of the three
     * model types (User, Client, Vendor) it points at: User and Client
     * both expose `name`, Vendor exposes `company_name` instead.
     */
    public function participantName(): string
    {
        $participant = $this->participant;

        if (! $participant) {
            return 'Unknown';
        }

        return $participant->name ?? $participant->company_name ?? 'Unknown';
    }

    /** A short label for the type badge ("User"/"Client"/"Vendor"). */
    public function participantTypeLabel(): string
    {
        return match ($this->participant_type) {
            Client::class => 'Client',
            Vendor::class => 'Vendor',
            User::class => 'User',
            default => class_basename($this->participant_type),
        };
    }
}
