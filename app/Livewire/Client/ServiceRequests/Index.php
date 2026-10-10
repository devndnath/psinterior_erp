<?php

namespace App\Livewire\Client\ServiceRequests;

use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.client-portal', ['title' => 'Service Requests'])]
#[Title('Service Requests')]
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

        return view('livewire.client.service-requests.index', [
            'serviceRequests' => ServiceRequest::where('client_id', $client->id)
                ->with('warranty.project')
                ->orderByDesc('created_at')
                ->paginate(10),
        ]);
    }
}
