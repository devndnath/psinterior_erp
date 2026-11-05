<?php

namespace App\Reports\Definitions;

use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Reports\Filters;
use App\Reports\Report;

/** Every client with their projects, billing and what they still owe. */
class ClientReport extends Report
{
    public static function key(): string { return 'client-report'; }
    public static function title(): string { return 'Client Report'; }
    public static function group(): string { return 'crm'; }
    public static function description(): string { return 'Every client — projects, project value, invoiced, paid and due, with who referred them.'; }

    public function filters(): array { return ['client']; }
    public function defaultRange(): string { return 'all'; }

    public function note(): ?string
    {
        return 'Lifetime figures. Project value = contract value (or estimated budget before a contract). Due = invoiced − paid on non-cancelled invoices.';
    }

    public function columns(): array
    {
        return [
            'client' => ['label' => 'Client', 'type' => 'text'],
            'type' => ['label' => 'Type', 'type' => 'text'],
            'reference' => ['label' => 'Referred by', 'type' => 'text'],
            'projects' => ['label' => 'Projects', 'type' => 'int', 'total' => true],
            'value' => ['label' => 'Project value', 'type' => 'money', 'total' => true],
            'invoiced' => ['label' => 'Invoiced', 'type' => 'money', 'total' => true],
            'paid' => ['label' => 'Paid', 'type' => 'money', 'total' => true],
            'due' => ['label' => 'Due', 'type' => 'money', 'total' => true],
        ];
    }

    public function rows(Filters $f): iterable
    {
        $clients = Client::query()->when($f->client, fn ($q) => $q->whereKey($f->client))
            ->with(['referenceClient:id,name', 'referenceEmployee:id,name'])->orderBy('name')->get();
        $ids = $clients->pluck('id');

        $projects = Project::whereIn('client_id', $ids)->selectRaw('client_id, COUNT(*) as n, SUM(COALESCE(contract_value, estimated_budget, 0)) as v')->groupBy('client_id')->get()->keyBy('client_id');
        $invoices = Invoice::whereIn('client_id', $ids)->where('status', '!=', InvoiceStatus::Cancelled->value)
            ->selectRaw('client_id, SUM(grand_total) as billed, SUM(paid_amount) as settled')->groupBy('client_id')->get()->keyBy('client_id');
        $paid = Payment::whereIn('client_id', $ids)->selectRaw('client_id, SUM(amount) as v')->groupBy('client_id')->pluck('v', 'client_id');

        foreach ($clients as $c) {
            $inv = $invoices[$c->id] ?? null;
            yield [
                'client' => self::cell($c->name, route('clients.show', $c), trim($c->client_no.' · '.($c->phone ?? ''), ' ·')),
                'type' => $c->type->label(),
                'reference' => $c->referrerName() ?? '—',
                'projects' => (int) ($projects[$c->id]->n ?? 0),
                'value' => (float) ($projects[$c->id]->v ?? 0),
                'invoiced' => (float) ($inv->billed ?? 0),
                'paid' => (float) ($paid[$c->id] ?? 0),
                'due' => max(0, (float) ($inv->billed ?? 0) - (float) ($inv->settled ?? 0)),
            ];
        }
    }
}
