<?php

namespace App\Policies;

use App\Models\SiteVisit;
use App\Models\User;

class SiteVisitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_site_visits');
    }

    public function view(User $user, SiteVisit $siteVisit): bool
    {
        return $user->can('view_site_visits');
    }

    public function create(User $user): bool
    {
        return $user->can('create_site_visits');
    }

    public function update(User $user, SiteVisit $siteVisit): bool
    {
        return $user->can('edit_site_visits');
    }

    public function delete(User $user, SiteVisit $siteVisit): bool
    {
        return $user->can('delete_site_visits');
    }
}
