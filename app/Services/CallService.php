<?php

namespace App\Services;

use App\Enums\CallOutcome;
use App\Enums\FollowupStatus;
use App\Models\Client;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Logging, scheduling and finishing calls for leads and clients — the
 * one place every call screen (lead page, client page, Calls page, CRM
 * overview) goes through, so they all behave the same way.
 */
class CallService
{
    public function __construct(protected LeadService $leads) {}

    /**
     * Log a call / follow-up for a lead or a client. A call logged as
     * done with a next follow-up date also schedules that next call, so
     * it shows up in everyone's "calls to make" on that day.
     */
    public function log(Lead|Client $contact, array $data): LeadFollowup
    {
        return DB::transaction(function () use ($contact, $data) {
            $data['status'] ??= FollowupStatus::Pending->value;

            if ($contact instanceof Lead) {
                // A converted lead's calls belong to its client too.
                $data['client_id'] = $contact->converted_client_id;
                $call = $this->leads->addFollowup($contact, $data);
            } else {
                $call = $contact->followups()->create($data);
            }

            if ($call->status !== FollowupStatus::Pending && $call->next_followup_date) {
                $this->scheduleNext($call, $call->next_followup_date->toDateString(), null);
            }

            return $call;
        });
    }

    /**
     * Finish a scheduled call: record how it went, add the result to its
     * notes, and optionally book the next call. No answer / busy / wrong
     * number marks it missed rather than done.
     */
    public function complete(LeadFollowup $call, ?CallOutcome $outcome, ?string $result = null, ?string $nextDate = null, ?string $nextTime = null): LeadFollowup
    {
        return DB::transaction(function () use ($call, $outcome, $result, $nextDate, $nextTime) {
            $notes = trim(collect([$call->notes, $result ? 'Result: '.$result : null])->filter()->implode("\n"));

            $call->update([
                'status' => $outcome && ! $outcome->reached() ? FollowupStatus::Missed : FollowupStatus::Done,
                'outcome' => $outcome,
                'notes' => $notes !== '' ? $notes : null,
                'next_followup_date' => $nextDate ?: $call->next_followup_date,
            ]);

            if ($nextDate) {
                $this->scheduleNext($call, $nextDate, $nextTime);
            }

            return $call;
        });
    }

    /** A new pending call for the same person, type and assignee. */
    public function scheduleNext(LeadFollowup $call, string $date, ?string $time): LeadFollowup
    {
        return LeadFollowup::create([
            'lead_id' => $call->lead_id,
            'client_id' => $call->client_id,
            'date' => $date,
            'time' => $time ?: null,
            'type' => $call->type,
            'assigned_to' => $call->assigned_to,
            'status' => FollowupStatus::Pending,
            'notes' => 'Follow-up to the '.$call->type->label().' on '.$call->date->format('d M, Y').'.',
        ]);
    }

    /** Lead calls need edit_leads, client calls edit_clients — either will do for a converted lead's. */
    public function canManage(?User $user, LeadFollowup $call): bool
    {
        return (bool) $user && (($call->client_id && $user->can('edit_clients')) || ($call->lead_id && $user->can('edit_leads')));
    }
}
