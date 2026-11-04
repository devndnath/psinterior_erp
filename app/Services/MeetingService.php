<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

/**
 * Owns the Meeting aggregate, including its polymorphic participant
 * list. $participantRefs is an array of {type: 'user'|'client'|'vendor',
 * id: int}; each is resolved to its model and attached via
 * Meeting::addParticipant().
 */
class MeetingService
{
    public function create(array $data, array $participantRefs = []): Meeting
    {
        return DB::transaction(function () use ($data, $participantRefs) {
            $meeting = Meeting::create([
                'project_id' => $data['project_id'] ?? null,
                'type' => $data['type'],
                'date' => $data['date'],
                'time' => $data['time'] ?? null,
                'location' => $data['location'] ?? null,
                'agenda' => $data['agenda'] ?? null,
                'discussion' => $data['discussion'] ?? null,
                'decisions' => $data['decisions'] ?? null,
                'follow_up' => $data['follow_up'] ?? null,
            ]);

            $this->syncParticipants($meeting, $participantRefs);

            return $meeting->fresh(['participants']);
        });
    }

    public function update(Meeting $meeting, array $data, array $participantRefs = []): Meeting
    {
        return DB::transaction(function () use ($meeting, $data, $participantRefs) {
            $meeting->update([
                'project_id' => array_key_exists('project_id', $data) ? $data['project_id'] : $meeting->project_id,
                'type' => array_key_exists('type', $data) ? $data['type'] : $meeting->type->value,
                'date' => array_key_exists('date', $data) ? $data['date'] : $meeting->date,
                'time' => array_key_exists('time', $data) ? $data['time'] : $meeting->time,
                'location' => array_key_exists('location', $data) ? $data['location'] : $meeting->location,
                'agenda' => array_key_exists('agenda', $data) ? $data['agenda'] : $meeting->agenda,
                'discussion' => array_key_exists('discussion', $data) ? $data['discussion'] : $meeting->discussion,
                'decisions' => array_key_exists('decisions', $data) ? $data['decisions'] : $meeting->decisions,
                'follow_up' => array_key_exists('follow_up', $data) ? $data['follow_up'] : $meeting->follow_up,
            ]);

            $this->syncParticipants($meeting, $participantRefs);

            return $meeting->fresh(['participants']);
        });
    }

    /** Delete-and-recreate, the same pattern used for items elsewhere in this codebase. */
    protected function syncParticipants(Meeting $meeting, array $participantRefs): void
    {
        $meeting->participants()->delete();

        foreach ($participantRefs as $ref) {
            $model = $this->resolveParticipant($ref['type'] ?? '', (int) ($ref['id'] ?? 0));

            if ($model) {
                $meeting->addParticipant($model);
            }
        }
    }

    protected function resolveParticipant(string $type, int $id): ?\Illuminate\Database\Eloquent\Model
    {
        return match ($type) {
            'user' => User::find($id),
            'client' => Client::find($id),
            'vendor' => Vendor::find($id),
            default => null,
        };
    }
}
