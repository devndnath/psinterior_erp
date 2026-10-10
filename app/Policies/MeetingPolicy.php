<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;

class MeetingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_meetings');
    }

    public function view(User $user, Meeting $meeting): bool
    {
        return $user->isStaff() && $user->can('view_meetings');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_meetings');
    }

    public function update(User $user, Meeting $meeting): bool
    {
        return $user->isStaff() && $user->can('edit_meetings');
    }

    public function delete(User $user, Meeting $meeting): bool
    {
        return $user->isStaff() && $user->can('delete_meetings');
    }
}
