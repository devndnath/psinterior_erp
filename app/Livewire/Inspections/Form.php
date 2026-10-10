<?php

namespace App\Livewire\Inspections;

use App\Enums\InspectionResult;
use App\Models\Inspection;
use App\Models\Project;
use App\Models\User;
use App\Services\InspectionService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Inspection $inspection = null;

    public string $project_id = '';
    public string $area = '';
    public string $inspection_date = '';
    public string $inspector_id = '';
    public string $notes = '';
    public string $result_override = '';

    /** @var array<int, array{checklist_item:string,result:string,notes:string}> */
    public array $items = [];

    public function mount(?Inspection $inspection = null): void
    {
        $this->inspection = $inspection?->exists ? $inspection : null;

        Gate::authorize($this->inspection ? 'update' : 'create', $this->inspection ?? Inspection::class);

        if ($this->inspection) {
            $this->fill([
                'project_id' => (string) $this->inspection->project_id,
                'area' => (string) $this->inspection->area,
                'inspection_date' => $this->inspection->inspection_date->toDateString(),
                'inspector_id' => (string) $this->inspection->inspector_id,
                'notes' => (string) $this->inspection->notes,
            ]);

            $this->items = $this->inspection->items->map(fn ($item) => [
                'checklist_item' => $item->checklist_item,
                'result' => $item->result->value,
                'notes' => (string) $item->notes,
            ])->all();
        } else {
            $projectId = request()->query('project');

            if ($projectId && Project::whereKey($projectId)->exists()) {
                $this->project_id = (string) $projectId;
            }

            $this->inspection_date = now()->toDateString();
        }

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function addItem(): void
    {
        $this->items[] = ['checklist_item' => '', 'result' => InspectionResult::Passed->value, 'notes' => ''];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    protected function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'area' => ['nullable', 'string', 'max:255'],
            'inspection_date' => ['required', 'date'],
            'inspector_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'result_override' => ['nullable', 'string', 'in:'.implode(',', array_column(InspectionResult::cases(), 'value'))],
            'items.*.checklist_item' => ['required', 'string', 'max:255'],
            'items.*.result' => ['required', 'string', 'in:'.implode(',', array_column(InspectionResult::cases(), 'value'))],
            'items.*.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function save(InspectionService $service): void
    {
        $data = $this->validate();

        $inspectionData = [
            'area' => $data['area'] ?: null,
            'inspection_date' => $data['inspection_date'],
            'inspector_id' => $data['inspector_id'] ?: null,
            'notes' => $data['notes'] ?: null,
        ];

        if ($data['result_override']) {
            $inspectionData['result'] = $data['result_override'];
        }

        if ($this->inspection) {
            $inspection = $service->update($this->inspection, $inspectionData, $data['items']);
            $message = 'Inspection updated.';
        } else {
            $project = Project::findOrFail($data['project_id']);
            $inspection = $service->create($project, $inspectionData, $data['items']);
            $message = 'Inspection created.';
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('inspections.show', ['inspection' => $inspection->id], navigate: true);
    }

    #[Title('Inspection')]
    public function render()
    {
        return view('livewire.inspections.form', [
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
            'inspectors' => User::staff()->orderBy('name')->get(['id', 'name']),
            'results' => InspectionResult::options(),
        ]);
    }
}
