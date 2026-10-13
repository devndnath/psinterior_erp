<?php

namespace App\Services;

use App\Models\Project;
use App\Models\SiteReport;

/**
 * Owns the Daily Site Report / Site Diary module — closes the "no daily
 * progress log" audit gap. Unlike SiteVisit, a report carries no document
 * number of its own (same precedent as Meeting), so this service is a
 * plain create/update/delete wrapper rather than one that also allocates
 * a NumberingService sequence.
 */
class SiteReportService
{
    public function create(Project $project, array $data): SiteReport
    {
        return $project->siteReports()->create($data);
    }

    public function update(SiteReport $report, array $data): SiteReport
    {
        $report->update($data);

        return $report->fresh();
    }

    public function delete(SiteReport $report): void
    {
        $report->delete();
    }
}
