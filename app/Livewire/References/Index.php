<?php

namespace App\Livewire\References;

use App\Models\Client;
use App\Models\Lead;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * References — who brings the firm its clients. Every referrer recorded
 * on a client or a not-yet-converted lead (HasReference), grouped: an
 * existing client, a staff member, or another person (grouped by name +
 * phone). For each: the clients and open leads they brought.
 */
#[Layout('layouts.admin', ['title' => 'References'])]
#[Title('References')]
class Index extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    /** '' | client | employee | other */
    #[Url]
    public string $type = '';

    public function mount(): void
    {
        abort_unless(Auth::user()?->can('view_clients'), 403);
    }

    private function key($m): string
    {
        return match ($m->reference_type) {
            'client' => 'client:'.$m->reference_client_id,
            'employee' => 'employee:'.$m->reference_employee_id,
            default => 'other:'.Str::lower(trim((string) $m->reference_name)).'|'.preg_replace('/\D/', '', (string) $m->reference_phone),
        };
    }

    public function render()
    {
        $with = ['referenceClient:id,name,phone,client_no', 'referenceEmployee:id,name,phone'];

        $clients = Client::query()->whereNotNull('reference_type')->with($with)
            ->get(['id', 'name', 'client_no', 'reference_type', 'reference_client_id', 'reference_employee_id', 'reference_name', 'reference_phone']);
        $leads = Lead::query()->whereNotNull('reference_type')->whereNull('converted_client_id')->with($with)
            ->get(['id', 'name', 'lead_no', 'status', 'reference_type', 'reference_client_id', 'reference_employee_id', 'reference_name', 'reference_phone']);

        $referrers = $clients->concat($leads)
            ->groupBy(fn ($m) => $this->key($m))
            ->map(function (Collection $group) {
                $first = $group->first();
                $groupClients = $group->filter(fn ($m) => $m instanceof Client)->values();

                return [
                    'type' => $first->reference_type,
                    'kind' => $first->referrerKind(),
                    'name' => $first->referrerName() ?? '—',
                    'phone' => match ($first->reference_type) {
                        'client' => $first->referenceClient?->phone,
                        'employee' => $first->referenceEmployee?->phone,
                        default => $first->reference_phone,
                    },
                    'href' => $first->reference_type === 'client' && $first->referenceClient ? route('clients.show', $first->referenceClient) : null,
                    'clients' => $groupClients,
                    'leads' => $group->filter(fn ($m) => $m instanceof Lead)->values(),
                ];
            })
            ->when($this->type, fn ($c) => $c->where('type', $this->type))
            ->when($this->search, function ($c) {
                $q = Str::lower($this->search);

                return $c->filter(fn ($r) => Str::contains(Str::lower($r['name'].' '.$r['phone']), $q)
                    || $r['clients']->contains(fn ($m) => Str::contains(Str::lower($m->name), $q))
                    || $r['leads']->contains(fn ($m) => Str::contains(Str::lower($m->name), $q)));
            })
            ->sortByDesc(fn ($r) => [$r['clients']->count(), $r['leads']->count()])
            ->values();

        $totalClients = Client::count();

        return view('livewire.references.index', [
            'referrers' => $referrers,
            'summary' => [
                'referrers' => $referrers->count(),
                'clients' => $referrers->sum(fn ($r) => $r['clients']->count()),
                'leads' => $referrers->sum(fn ($r) => $r['leads']->count()),
                'share' => $totalClients > 0 ? round($clients->count() / $totalClients * 100) : 0,
            ],
        ]);
    }
}
