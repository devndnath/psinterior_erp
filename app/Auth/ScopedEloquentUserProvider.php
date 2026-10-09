<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Every guard (web/client/vendor) shares the `users` table but must only
 * ever authenticate rows of its own `user_type` (ARCHITECTURE.md §5, §7).
 * This provider adds that constraint at the three points credentials or an
 * ID could otherwise resolve to a user: login, "remember me" cookie
 * restoration, and credential re-validation. A client's correct password
 * tried against the staff login form will not resolve a user at all here —
 * it is not a permissions problem solved after the fact, the row is simply
 * invisible to the wrong guard.
 */
class ScopedEloquentUserProvider extends EloquentUserProvider
{
    public function __construct($hasher, $model, protected string $userType)
    {
        parent::__construct($hasher, $model);
    }

    public function retrieveById($identifier): ?Authenticatable
    {
        $user = parent::retrieveById($identifier);

        return $this->matchesType($user) ? $user : null;
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        $user = parent::retrieveByToken($identifier, $token);

        return $this->matchesType($user) ? $user : null;
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        $user = parent::retrieveByCredentials($credentials);

        return $this->matchesType($user) ? $user : null;
    }

    protected function matchesType(?Authenticatable $user): bool
    {
        if (! $user) {
            return false;
        }

        $type = $user->user_type instanceof \BackedEnum ? $user->user_type->value : $user->user_type;

        return $type === $this->userType && $user->is_active;
    }
}
