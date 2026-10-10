<?php

namespace App\Livewire\Meetings;

use App\Models\Meeting;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Meeting $meeting;

    public function mount(Meeting $meeting): void
    {
        Gate::authorize('view', $meeting);

        $this->meeting = $meeting;
    }

    public function deleteMeeting(): void
    {
        Gate::authorize('delete', $this->meeting);

        $this->meeting->delete();

        $this->dispatch('toast', message: 'Meeting deleted.', type: 'success');
        $this->redirectRoute('meetings.index', navigate: true);
    }

    #[Title('Meeting')]
    public function render()
    {
        $this->meeting->load(['project', 'participants.participant']);

        return view('livewire.meetings.show');
    }
}
