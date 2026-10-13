<?php

namespace App\Livewire\MaterialSelections;

use App\Models\Project;
use App\Models\SelectionBoard;
use App\Services\SelectionBoardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?SelectionBoard $board = null;

    public string $project_id = '';

    public string $area = '';

    public string $title = '';

    public string $description = '';

    public function mount(?SelectionBoard $board = null): void
    {
        $this->board = $board?->exists ? $board : null;

        Gate::authorize($this->board ? 'update' : 'create', $this->board ?? SelectionBoard::class);

        if ($this->board) {
            $this->fill([
                'project_id' => (string) $this->board->project_id,
                'area' => (string) $this->board->area,
                'title' => $this->board->title,
                'description' => (string) $this->board->description,
            ]);
        } else {
            $projectId = request()->query('project');

            if ($projectId && Project::whereKey($projectId)->exists()) {
                $this->project_id = (string) $projectId;
            }
        }
    }

    protected function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'area' => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function save(SelectionBoardService $service): void
    {
        $data = $this->validate();

        $boardData = [
            'project_id' => $data['project_id'],
            'area' => $data['area'] ?: null,
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
        ];

        if ($this->board) {
            // project_id is disabled on the form once a board exists (see
            // the Blade view) — the array key stays out of the update
            // payload entirely so a future change to that can't
            // accidentally move an already-shared board between projects.
            unset($boardData['project_id']);
            $board = $service->update($this->board, $boardData);
            $message = 'Selection updated.';
        } else {
            $board = $service->create($boardData, Auth::user());
            $message = 'Selection created. Add at least two options, then send it to the client.';
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('material-selections.show', ['board' => $board->id], navigate: true);
    }

    #[Title('Material Selection')]
    public function render()
    {
        return view('livewire.material-selections.form', [
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
