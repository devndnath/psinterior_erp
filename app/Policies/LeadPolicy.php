<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_leads');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->can('view_leads');
    }

    public function create(User $user): bool
    {
        return $user->can('create_leads');
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->can('edit_leads');
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->can('delete_leads');
    }

    public function convert(User $user, Lead $lead): bool
    {
        // Converting a lead creates a client record, so it needs both
        // abilities — a user who can edit leads but not create clients
        // should not be able to short-circuit client creation this way.
        return $user->can('edit_leads') && $user->can('create_clients');
    }
}
