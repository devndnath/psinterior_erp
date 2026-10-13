<?php

namespace App\Livewire\Client\SiteReports;

use App\Models\Project;
use App\Models\SiteReport;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Ownership check, not a staff Policy — same "Portal authorization
 * model" as every other client-portal component (PHASE11_SUMMARY.md).
 * Read-only daily progress feed: clients see every report for their own
 * projects, with no draft/visibility state to filter (unlike Selection
 * Boards) since a report is only ever created already-finished.
 */
#[Layout('layouts.client-portal', ['title' => 'Site Reports'])]
#[Title('Site Reports')]
class Index extends Component
{
    use WithPagination;

    public function mount(): void
    {
        abort_unless(Auth::guard('client')->user()->client, 403, 'No client record linked to this account.');
    }

    public function render()
    {
        $client = Auth::guard('client')->user()->client;
        $projectIds = Project::where('client_id', $client->id)->pluck('id');

        return view('livewire.client.site-reports.index', [
            'reports' => SiteReport::whereIn('project_id', $projectIds)
                ->with('project')
                ->orderByDesc('report_date')
                ->paginate(10),
        ]);
    }
}
