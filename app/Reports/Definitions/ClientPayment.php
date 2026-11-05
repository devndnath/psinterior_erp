<?php

namespace App\Reports\Definitions;

use App\Enums\InvoiceType;
use App\Models\Payment;
use App\Reports\Filters;
use App\Reports\Report;

/** Payments received, totalled per client. */
class ClientPayment extends Report
{
    public static function key(): string { return 'client-payment'; }
    public static function title(): string { return 'Client Wise Payment Report'; }
    public static function group(): string { return 'all'; }
    public static function permission(): string { return 'view_payments'; }
    public static function description(): string { return 'Payments received from each client — project and design sales separately — with the last payment date.'; }

    public function filters(): array { return ['dates', 'client', 'method']; }

    public function columns(): array
    {
        return [
            'client' => ['label' => 'Client', 'type' => 'text'],
            'payments' => ['label' => 'Payments', 'type' => 'int', 'total' => true],
            'project' => ['label' => 'Project payments', 'type' => 'money', 'total' => true],
            'design' => ['label' => 'Design sales', 'type' => 'money', 'total' => true],
            'total' => ['label' => 'Total received', 'type' => 'money', 'total' => true],
            'last' => ['label' => 'Last payment', 'type' => 'date'],
        ];
    }

    public function rows(Filters $f): iterable
    {
        $groups = $f->dates(Payment::query(), 'date')
            ->when($f->client, fn ($q) => $q->where('client_id', $f->client))
            ->when($f->method, fn ($q) => $q->where('method', $f->method))
            ->with(['client:id,name,client_no', 'invoice:id,type'])->get()->groupBy('client_id')
            ->sortByDesc(fn ($g) => $g->sum('amount'));

        foreach ($groups as $g) {
            $c = $g->first()->client;
            $design = $g->filter(fn ($p) => $p->project_id === null || $p->invoice?->type === InvoiceType::Design);
            yield [
                'client' => self::cell($c?->name ?? '—', $c ? route('clients.show', $c) : null, $c?->client_no),
                'payments' => $g->count(),
                'project' => (float) $g->sum('amount') - (float) $design->sum('amount'),
                'design' => (float) $design->sum('amount'),
                'total' => (float) $g->sum('amount'),
                'last' => $g->max('date'),
            ];
        }
    }
}
