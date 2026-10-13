<?php

namespace App\Models;

use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A recurring, project-scoped daily log entry ("what happened on site
 * today") — unlike SiteVisit (a one-off numbered scoping visit), this is
 * always tied to a project and carries no document number of its own,
 * same "no own number" precedent as Meeting. Project itself is already
 * company-scoped, so no separate HasCompanyScope here.
 */
class SiteReport extends Model
{
    use HasDocuments, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'project_id', 'reported_by', 'report_date', 'weather',
        'manpower_count', 'work_summary', 'materials_notes', 'issues', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
        ];
    }

    public function logAttributes(): array
    {
        return ['report_date', 'manpower_count', 'weather'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
