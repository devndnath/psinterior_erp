<?php

namespace App\Policies;

use App\Models\ActivityLog;
use App\Models\User;

/**
 * "Do not allow normal users to delete audit logs" (spec §49, §78 rule 6).
 * Only Super Admin — via the Gate::before bypass in AuthServiceProvider —
 * can ever delete or modify a row here; every other role is denied
 * outright, including users holding a generic "manage everything"
 * permission.
 */
class ActivityLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_reports');
    }

    public function view(User $user, ActivityLog $log): bool
    {
        return $user->isStaff() && $user->can('view_reports');
    }

    public function create(User $user): bool
    {
        return false; // written only by ActivityLogService, never by a user action directly
    }

    public function update(User $user, ActivityLog $log): bool
    {
        return false;
    }

    public function delete(User $user, ActivityLog $log): bool
    {
        return false;
    }
}
