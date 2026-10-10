<?php

namespace App\Livewire\Client\Documents;

use App\Models\Contract;
use App\Models\Design;
use App\Models\Document;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Quotation;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Lists documents attached to the client's OWN projects/quotations/
 * contracts/invoices/designs — a few sequential whereIn queries, one
 * per documentable type, kept simple per the brief rather than one
 * fragile cross-type union query. Ownership check, not a staff Policy
 * — see "Portal authorization model" in PHASE11_SUMMARY.md.
 */
#[Layout('layouts.client-portal', ['title' => 'Documents'])]
#[Title('Documents')]
class Index extends Component
{
    public function mount(): void
    {
        abort_unless(Auth::guard('client')->user()->client, 403, 'No client record linked to this account.');
    }

    public function render()
    {
        $client = Auth::guard('client')->user()->client;
        $projectIds = Project::where('client_id', $client->id)->pluck('id');

        $documents = collect()
            ->concat(Document::where('documentable_type', Project::class)->whereIn('documentable_id', $projectIds)->get())
            ->concat(Document::where('documentable_type', Design::class)->whereIn('documentable_id', Design::whereIn('project_id', $projectIds)->pluck('id'))->get())
            ->concat(Document::where('documentable_type', Quotation::class)->whereIn('documentable_id', Quotation::where('client_id', $client->id)->pluck('id'))->get())
            ->concat(Document::where('documentable_type', Contract::class)->whereIn('documentable_id', Contract::where('client_id', $client->id)->pluck('id'))->get())
            ->concat(Document::where('documentable_type', Invoice::class)->whereIn('documentable_id', Invoice::where('client_id', $client->id)->pluck('id'))->get())
            ->sortByDesc('created_at')
            ->values();

        return view('livewire.client.documents.index', ['documents' => $documents]);
    }
}
