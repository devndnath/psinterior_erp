<?php

namespace App\Livewire\Client\ChangeOrders;

use App\Models\ChangeOrder;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.client-portal', ['title' => 'Change Orders'])]
#[Title('Change Orders')]
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

        return view('livewire.client.change-orders.index', [
            'changeOrders' => ChangeOrder::whereIn('project_id', $projectIds)
                ->with('project')
                ->orderByDesc('created_at')
                ->paginate(10),
        ]);
    }
}
