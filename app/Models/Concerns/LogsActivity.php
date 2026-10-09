<?php

namespace App\Models\Concerns;

use App\Services\ActivityLogService;

/**
 * Attach to any model whose create/update/delete should appear in the
 * audit trail (spec §49: "Admin changed project budget", "Accountant
 * created invoice", ...). Override `$logAttributes` on the model to limit
 * which columns are diffed (omit large/sensitive ones); default is "all
 * fillable attributes".
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            app(ActivityLogService::class)->log(
                subject: $model,
                action: 'created',
                newValues: $model->only($model->logAttributes()),
            );
        });

        static::updated(function ($model) {
            $dirty = $model->getDirty();

            if (empty($dirty)) {
                return;
            }

            $tracked = array_intersect_key($dirty, array_flip($model->logAttributes()));

            if (empty($tracked)) {
                return;
            }

            $old = collect(array_keys($tracked))
                ->mapWithKeys(fn ($key) => [$key => $model->getOriginal($key)])
                ->all();

            app(ActivityLogService::class)->log(
                subject: $model,
                action: 'updated',
                oldValues: $old,
                newValues: $tracked,
            );
        });

        static::deleted(function ($model) {
            app(ActivityLogService::class)->log(
                subject: $model,
                action: method_exists($model, 'trashed') && $model->trashed() ? 'deleted' : 'force_deleted',
            );
        });
    }

    /**
     * Columns included in audit diffs. Defaults to the model's fillable
     * list; override per-model to narrow it (e.g. exclude `password`).
     */
    public function logAttributes(): array
    {
        return $this->fillable;
    }
}
