<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

class ContractPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_contracts');
    }

    public function view(User $user, Contract $contract): bool
    {
        return $user->isStaff() && $user->can('view_contracts');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_contracts');
    }

    public function update(User $user, Contract $contract): bool
    {
        return $user->isStaff() && $user->can('edit_contracts');
    }

    public function delete(User $user, Contract $contract): bool
    {
        return $user->isStaff() && $user->can('delete_contracts');
    }
}
