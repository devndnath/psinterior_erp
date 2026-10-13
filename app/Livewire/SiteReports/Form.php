<?php

namespace App\Livewire\SiteReports;

use App\Models\Project;
use App\Models\SiteReport;
use App\Services\FileService;
use App\Services\SiteReportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Create/edit a daily site report, with optional photo uploads attached
 * once the report exists (FileService needs a persisted model to attach
 * documents to) — same shape as SiteVisits/Form.
 */
#[Layout('layouts.admin')]
class Form extends Component
{
    use WithFileUploads;

    public ?SiteReport $siteReport = null;

    public string $project_id = '';

    public string $report_date = '';

    public string $weather = '';

    public string $manpower_count = '';

    public string $work_summary = '';

    public string $materials_notes = '';

    public string $issues = '';

    public string $notes = '';

    /** @var \Illuminate\Http\UploadedFile[] */
    public array $photos = [];

    public function mount(?SiteReport $siteReport = null): void
    {
        $this->siteReport = $siteReport?->exists ? $siteReport : null;

        Gate::authorize($this->siteReport ? 'update' : 'create', $this->siteReport ?? SiteReport::class);

        if ($this->siteReport) {
            $this->fill([
                'project_id' => (string) $this->siteReport->project_id,
                'report_date' => $this->siteReport->report_date?->format('Y-m-d') ?? '',
                'weather' => (string) $this->siteReport->weather,
                'manpower_count' => (string) $this->siteReport->manpower_count,
                'work_summary' => $this->siteReport->work_summary,
                'materials_notes' => (string) $this->siteReport->materials_notes,
                'issues' => (string) $this->siteReport->issues,
                'notes' => (string) $this->siteReport->notes,
            ]);
        } else {
            $this->report_date = now()->toDateString();

            // Pre-fill from a "Log Site Report" link on a Project page.
            $projectId = request()->query('project');

            if ($projectId && Project::find($projectId)) {
                $this->project_id = (string) $projectId;
            }
        }
    }

    protected function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'report_date' => ['required', 'date'],
            'weather' => ['nullable', 'string', 'max:100'],
            'manpower_count' => ['nullable', 'integer', 'min:0'],
            'work_summary' => ['required', 'string', 'max:4000'],
            'materials_notes' => ['nullable', 'string', 'max:2000'],
            'issues' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'photos.*' => ['nullable', 'image', 'max:10240'],
        ];
    }

    public function save(SiteReportService $service, FileService $files): void
    {
        $data = $this->validate();

        $reportData = [
            'reported_by' => $this->siteReport?->reported_by ?? Auth::id(),
            'report_date' => $data['report_date'],
            'weather' => $data['weather'] ?: null,
            'manpower_count' => $data['manpower_count'] !== '' ? $data['manpower_count'] : null,
            'work_summary' => $data['work_summary'],
            'materials_notes' => $data['materials_notes'] ?: null,
            'issues' => $data['issues'] ?: null,
            'notes' => $data['notes'] ?: null,
        ];

        if ($this->siteReport) {
            $report = $service->update($this->siteReport, $reportData);
            $message = 'Site report updated.';
        } else {
            $project = Project::findOrFail($data['project_id']);
            $report = $service->create($project, $reportData);
            $message = 'Site report created.';
        }

        foreach ($this->photos as $photo) {
            $files->store($photo, $report, 'site_report_photo');
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('site-reports.show', ['siteReport' => $report->id], navigate: true);
    }

    #[Title('Site Report')]
    public function render()
    {
        return view('livewire.site-reports.form', [
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
