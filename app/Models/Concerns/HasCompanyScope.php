<?php

namespace App\Models\Concerns;

use App\Models\Scopes\CompanyScope;

/**
 * Attach to any company-scoped model (users, departments, and — in later
 * phases — leads, clients, projects, etc.). Today there is one company row
 * and the scope is a no-op in practice, but it means multi-company support
 * is a config flip, not a rewrite (ARCHITECTURE.md §1 assumption 2 / spec
 * §74).
 */
trait HasCompanyScope
{
    public static function bootHasCompanyScope(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function ($model) {
            if (! $model->company_id && auth()->check() && auth()->user()->company_id) {
                $model->company_id = auth()->user()->company_id;
            }
        });
    }
}
