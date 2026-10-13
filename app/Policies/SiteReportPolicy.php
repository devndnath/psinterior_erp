<?php

namespace App\Policies;

use App\Models\SiteReport;
use App\Models\User;

class SiteReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_site_reports');
    }

    public function view(User $user, SiteReport $siteReport): bool
    {
        return $user->can('view_site_reports');
    }

    public function create(User $user): bool
    {
        return $user->can('create_site_reports');
    }

    public function update(User $user, SiteReport $siteReport): bool
    {
        return $user->can('edit_site_reports');
    }

    public function delete(User $user, SiteReport $siteReport): bool
    {
        return $user->can('delete_site_reports');
    }
}
