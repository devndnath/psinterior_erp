<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Models\Client;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Notifications\LeadConvertedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeadService
{
    public function __construct(
        protected NumberingService $numbering,
        protected ClientService $clients,
    ) {}

    public function create(array $data): Lead
    {
        $data['lead_no'] = $this->numbering->next('LEAD');

        return Lead::create($data);
    }

    public function update(Lead $lead, array $data): Lead
    {
        $lead->update($data);

        return $lead->fresh();
    }

    public function addFollowup(Lead $lead, array $data): LeadFollowup
    {
        $followup = $lead->followups()->create($data);

        // A follow-up logged against a brand-new lead is itself a status
        // signal: a lead with no contact yet moves from New to Follow-up
        // the moment the first touch is recorded, so the pipeline reflects
        // reality without someone manually flipping the status field too.
        if ($lead->status === LeadStatus::New) {
            $lead->update(['status' => LeadStatus::FollowUp]);
        }

        return $followup;
    }

    /**
     * "Convert to Client & Project" (spec §5). Phase 2 only creates the
     * Client half — project creation is wired in once Phase 3 ships
     * App\Models\Project, at which point this method gains a
     * $createProject parameter rather than being rewritten.
     *
     * Never duplicates data: if this lead already converted, returns the
     * existing client instead of creating a second one.
     */
    public function convertToClient(Lead $lead, array $overrides = []): Client
    {
        if ($lead->isConverted()) {
            return $lead->convertedClient;
        }

        if ($lead->status !== LeadStatus::Won) {
            throw ValidationException::withMessages([
                'status' => 'Only a lead marked as Won can be converted to a client.',
            ]);
        }

        return DB::transaction(function () use ($lead, $overrides) {
            $client = $this->clients->create(array_merge([
                'name' => $lead->name,
                'type' => \App\Enums\ClientType::Individual->value,
                'phone' => $lead->phone,
                'email' => $lead->email,
                'company_name' => $lead->company_name,
                'address' => $lead->address,
                'assigned_to' => $lead->assigned_to,
                'notes' => "Converted from lead {$lead->lead_no}.",
            ], $overrides));

            $lead->update([
                'converted_client_id' => $client->id,
                'converted_at' => now(),
            ]);

            // Database-channel notification only (Phase 11) — the lead's
            // own assignee is the one person who already has the context
            // to act on this conversion (e.g. kick off a Project next).
            $lead->assignee?->notify(new LeadConvertedNotification($lead, $client));

            return $client;
        });
    }
}
