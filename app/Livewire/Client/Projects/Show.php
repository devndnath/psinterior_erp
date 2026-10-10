<?php

namespace App\Livewire\Client\Projects;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Ownership check, not a staff Policy — see "Portal authorization
 * model" in PHASE11_SUMMARY.md. Read-only: milestones/tasks progress.
 */
#[Layout('layouts.client-portal')]
class Show extends Component
{
    public Project $project;

    public function mount(Project $project): void
    {
        $client = Auth::guard('client')->user()->client;
        abort_unless($client, 403, 'No client record linked to this account.');
        abort_unless($project->client_id === $client->id, 403);

        $this->project = $project;
    }

    #[Title('Project')]
    public function render()
    {
        $this->project->load(['milestones', 'tasks', 'projectManager']);

        return view('livewire.client.projects.show');
    }
}
