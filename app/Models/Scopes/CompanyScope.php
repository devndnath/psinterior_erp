<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Scope;

/**
 * Scopes a query to the logged-in user's company, once more than one
 * company row exists. With a single company (today) this scope matches
 * every row, since every seeded record shares the same company_id.
 * Super Admins (no company_id, or the `view_all_companies` permission —
 * added when multi-company ships) bypass it.
 */
class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! auth()->check()) {
            return;
        }

        $user = auth()->user();

        if (! $user->company_id) {
            return;
        }

        if (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('view_all_companies')) {
            return;
        }

        $builder->where($model->getTable().'.company_id', $user->company_id);
    }
}
