<?php

namespace App\Livewire\Reports;

use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin', ['title' => 'Project Status Report'])]
#[Title('Project Status Report')]
class ProjectStatus extends Component
{
    public string $status = '';

    protected function projects()
    {
        return Project::query()
            ->with('client')
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderBy('name')
            ->get();
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $projects = $this->projects();

        return response()->streamDownload(function () use ($projects) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Code', 'Name', 'Client', 'Status', 'Progress %', 'Contract Value', 'Start Date', 'Expected End', 'Actual End']);

            foreach ($projects as $project) {
                fputcsv($out, [
                    $project->project_code,
                    $project->name,
                    $project->client?->name,
                    $project->status->label(),
                    $project->progress,
                    $project->contract_value,
                    $project->start_date?->toDateString(),
                    $project->expected_end_date?->toDateString(),
                    $project->actual_end_date?->toDateString(),
                ]);
            }

            fclose($out);
        }, 'project-status-report.csv');
    }

    public function render()
    {
        return view('livewire.reports.project-status', [
            'projects' => $this->projects(),
            'statusOptions' => \App\Enums\ProjectStatus::options(),
        ]);
    }
}
