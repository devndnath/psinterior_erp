<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Fail loudly on an N+1 in local/testing instead of shipping it
        // (spec §59 — "avoid N+1 queries").
        Model::shouldBeStrict(! app()->isProduction());

        Paginator::useTailwind();

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
