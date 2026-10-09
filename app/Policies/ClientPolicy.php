<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_clients');
    }

    public function view(User $user, Client $client): bool
    {
        return $user->can('view_clients');
    }

    public function create(User $user): bool
    {
        return $user->can('create_clients');
    }

    public function update(User $user, Client $client): bool
    {
        return $user->can('edit_clients');
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->can('delete_clients');
    }
}
