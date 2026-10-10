<?php

namespace App\Livewire\Meetings;

use App\Enums\MeetingType;
use App\Models\Meeting;
use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Meetings'])]
#[Title('Meetings')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $type = '';

    #[Url]
    public string $project = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Meeting::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['type', 'project'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['type', 'project']);
    }

    public function deleteMeeting(int $meetingId): void
    {
        $meeting = Meeting::findOrFail($meetingId);
        Gate::authorize('delete', $meeting);

        $meeting->delete();

        $this->dispatch('toast', message: 'Meeting deleted.', type: 'success');
    }

    public function render()
    {
        $meetings = Meeting::query()
            ->with('project')
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
            ->latest('date')
            ->paginate(15);

        return view('livewire.meetings.index', [
            'meetings' => $meetings,
            'types' => MeetingType::options(),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
