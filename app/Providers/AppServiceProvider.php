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

        $this->scrubInvalidUtf8OnRetrieve();
    }

    /**
     * Read-side counterpart to SanitizeUtf8Input: that middleware stops any
     * *new* invalid UTF-8 byte sequence from ever being saved, but a record
     * written before the middleware existed can already have one sitting in
     * the database (e.g. invoice_items.description on an invoice entered
     * early on). Livewire re-serializes a component's full state to JSON on
     * every render, and json_encode() throws InvalidArgumentException
     * ("Malformed UTF-8 characters, possibly incorrectly encoded") the
     * instant it meets that byte — which 500s every further interaction
     * with that record, confirmed via a stack trace where
     * SanitizeUtf8Input had already run cleanly, so the only place left
     * the bad bytes could be coming from is the database itself.
     *
     * A global `Model::retrieved()` listener (fires for every Eloquent
     * model of every class, not just one) is the single point that covers
     * every table without migrating or guessing which column is affected.
     * setRawAttributes(..., true) rewrites the in-memory copy only — the
     * second `true` syncs Eloquent's "original" snapshot to match, so this
     * never marks the model dirty or risks an unrelated save() silently
     * rewriting a row that was never meant to change.
     */
    private function scrubInvalidUtf8OnRetrieve(): void
    {
        Model::retrieved(function (Model $model) {
            $attributes = $model->getAttributes();
            $fixed = null;

            foreach ($attributes as $key => $value) {
                if (is_string($value) && $value !== '' && ! mb_check_encoding($value, 'UTF-8')) {
                    $fixed ??= $attributes;
                    $fixed[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                }
            }

            if ($fixed !== null) {
                $model->setRawAttributes($fixed, true);
            }
        });
    }
}
