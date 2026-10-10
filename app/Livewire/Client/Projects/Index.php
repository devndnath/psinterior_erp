<?php

namespace App\Livewire\Client\Projects;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.client-portal', ['title' => 'My Projects'])]
#[Title('My Projects')]
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

        return view('livewire.client.projects.index', [
            'projects' => Project::where('client_id', $client->id)->orderByDesc('created_at')->paginate(10),
        ]);
    }
}
