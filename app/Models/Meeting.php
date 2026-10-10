<?php

namespace App\Models;

use App\Enums\MeetingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'type', 'date', 'time', 'location',
        'agenda', 'discussion', 'decisions', 'follow_up',
    ];

    protected function casts(): array
    {
        return [
            'type' => MeetingType::class,
            'date' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(MeetingParticipant::class);
    }

    /**
     * Sets participant_id/participant_type from the given model, which
     * may be a User, Client or Vendor instance.
     */
    public function addParticipant(Model $participant): MeetingParticipant
    {
        return $this->participants()->create([
            'participant_id' => $participant->getKey(),
            'participant_type' => $participant::class,
        ]);
    }
}
