<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;

/**
 * Permission-gating decision (see RolePermissionSeeder and
 * PHASE10_SUMMARY.md for the full writeup): `RolePermissionSeeder`'s
 * `$crudModules` does NOT include `service_requests` — there is no
 * `view_service_requests`/`edit_service_requests`/etc. Rather than
 * invent an unseeded permission string (which would silently lock out
 * every non-super-admin role), every ability here is gated on the
 * existing, real `*_warranties` permissions instead: a Service Request
 * always belongs to a Warranty and is conceptually the same
 * post-handover-support permission domain.
 *
 * viewAny/view -> view_warranties, create -> create_warranties,
 * update and every status-transition action -> edit_warranties,
 * delete -> delete_warranties.
 */
class ServiceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_warranties');
    }

    public function view(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->isStaff() && $user->can('view_warranties');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_warranties');
    }

    public function update(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->isStaff() && $user->can('edit_warranties');
    }

    public function delete(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->isStaff() && $user->can('delete_warranties');
    }

    public function assignTechnician(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->isStaff() && $user->can('edit_warranties');
    }

    public function scheduleSiteVisit(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->isStaff() && $user->can('edit_warranties');
    }

    public function markRepair(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->isStaff() && $user->can('edit_warranties');
    }

    public function markVerification(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->isStaff() && $user->can('edit_warranties');
    }

    public function close(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->isStaff() && $user->can('edit_warranties');
    }
}
