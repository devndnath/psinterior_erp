<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_attendance');
    }

    public function view(User $user, Attendance $attendance): bool
    {
        return $user->isStaff() && $user->can('view_attendance');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_attendance');
    }

    public function update(User $user, Attendance $attendance): bool
    {
        return $user->isStaff() && $user->can('edit_attendance');
    }

    public function delete(User $user, Attendance $attendance): bool
    {
        return $user->isStaff() && $user->can('delete_attendance');
    }
}
