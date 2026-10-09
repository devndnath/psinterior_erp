<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Single write path into `activity_logs` (spec §49). Called directly for
 * ad-hoc business events ("Client approved design") and automatically by
 * the App\Models\Concerns\LogsActivity trait for plain create/update/delete.
 */
class ActivityLogService
{
    public function log(
        Model $subject,
        string $action,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?Model $causer = null,
    ): ActivityLog {
        $causer ??= auth()->user();

        return ActivityLog::create([
            'causer_id' => $causer?->id,
            'subject_id' => $subject->getKey(),
            'subject_type' => $subject->getMorphClass(),
            'action' => $action,
            'description' => $description ?? $this->defaultDescription($subject, $action, $causer),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'created_at' => now(),
        ]);
    }

    protected function defaultDescription(Model $subject, string $action, ?Model $causer): string
    {
        $who = $causer?->name ?? 'System';
        $what = class_basename($subject);

        return "{$who} {$action} {$what} #{$subject->getKey()}";
    }
}
