<?php

namespace App\Livewire\Client\Invoices;

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.client-portal', ['title' => 'Invoices'])]
#[Title('Invoices')]
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

        return view('livewire.client.invoices.index', [
            'invoices' => Invoice::where('client_id', $client->id)
                ->with('project')
                ->orderByDesc('invoice_date')
                ->paginate(10),
        ]);
    }
}
