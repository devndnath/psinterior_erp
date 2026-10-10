<?php

namespace App\Livewire\Client\Quotations;

use App\Models\Quotation;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.client-portal', ['title' => 'Quotations'])]
#[Title('Quotations')]
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

        return view('livewire.client.quotations.index', [
            'quotations' => Quotation::where('client_id', $client->id)
                ->with('project')
                ->orderByDesc('created_at')
                ->paginate(10),
        ]);
    }
}
