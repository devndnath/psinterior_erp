<?php

namespace App\Livewire\Client\Selections;

use App\Models\Project;
use App\Models\SelectionBoard;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Ownership check, not a staff Policy — same "Portal authorization
 * model" as every other client-portal component (PHASE11_SUMMARY.md).
 * A Draft board is deliberately excluded here: it's still being
 * assembled by staff and isn't a real choice yet, so the client only
 * ever sees Sent (decide now) and Decided (what was chosen) boards.
 */
#[Layout('layouts.client-portal', ['title' => 'Selections'])]
#[Title('Selections')]
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

        return view('livewire.client.selections.index', [
            'boards' => SelectionBoard::whereIn('project_id', $projectIds)
                ->whereIn('status', ['sent', 'decided'])
                ->with('project')
                ->orderByDesc('created_at')
                ->paginate(10),
        ]);
    }
}
