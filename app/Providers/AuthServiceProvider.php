<?php

namespace App\Providers;

use App\Auth\ScopedEloquentUserProvider;
use App\Models\ActivityLog;
use App\Models\Boq;
use App\Models\ChangeOrder;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Design;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\SiteVisit;
use App\Policies\ActivityLogPolicy;
use App\Policies\BoqPolicy;
use App\Policies\ChangeOrderPolicy;
use App\Policies\ClientPolicy;
use App\Policies\ContractPolicy;
use App\Policies\DesignPolicy;
use App\Policies\LeadPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\QuotationPolicy;
use App\Policies\SiteVisitPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Model => Policy map. Every module adds its own model/policy pair
     * here as it's built (ARCHITECTURE.md §6/§61 — "use middleware/
     * policies/gates properly", never hard-code permission checks inside
     * controllers).
     */
    protected $policies = [
        ActivityLog::class => ActivityLogPolicy::class,
        Lead::class => LeadPolicy::class,
        Client::class => ClientPolicy::class,
        SiteVisit::class => SiteVisitPolicy::class,
        Project::class => ProjectPolicy::class,
        Design::class => DesignPolicy::class,
        Boq::class => BoqPolicy::class,
        Quotation::class => QuotationPolicy::class,
        Contract::class => ContractPolicy::class,
        ChangeOrder::class => ChangeOrderPolicy::class,
    ];

    public function register(): void {}

    public function boot(): void
    {
        $this->registerPolicies();

        // Backs config/auth.php's `scoped_eloquent` provider driver —
        // guarantees the `web`, `client` and `vendor` guards can only ever
        // authenticate a `users` row of their own user_type.
        Auth::provider('scoped_eloquent', function ($app, array $config) {
            return new ScopedEloquentUserProvider(
                $app['hash'],
                $config['model'],
                $config['user_type'],
            );
        });

        // Super Admin bypasses every permission check outright, matching
        // spec §3 ("Do NOT hard-code permissions inside controllers" — this
        // is the one deliberate, centrally-declared exception).
        Gate::before(function ($user, string $ability) {
            return $user->isStaff() && $user->hasRole('super-admin') ? true : null;
        });
    }
}
