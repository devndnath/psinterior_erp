<?php

namespace App\Livewire\Reports;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadSource;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin', ['title' => 'Sales Funnel Report'])]
#[Title('Sales Funnel Report')]
class SalesFunnel extends Component
{
    public string $dateFrom = '';
    public string $dateTo = '';

    protected function leadsQuery()
    {
        return Lead::query()
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo));
    }

    protected function byStatus(): array
    {
        // Not ->pluck('total', 'status'): Lead::query()->get() hydrates
        // Eloquent models, so 'status' is cast to the LeadStatus backed enum
        // (see Lead::casts()). An enum object as an array key throws
        // "Illegal offset type" (Illuminate\Support\Arr::pluck). Unwrap
        // ->value explicitly instead — same bug class fixed in
        // Reports\Hr::attendanceByEmployee() and Dashboard\Index::render().
        $counts = $this->leadsQuery()->selectRaw('status, count(*) as total')->groupBy('status')->get()
            ->mapWithKeys(fn ($row) => [
                $row->status instanceof \BackedEnum ? $row->status->value : $row->status => $row->total,
            ]);

        return collect(LeadStatus::cases())->mapWithKeys(fn (LeadStatus $status) => [
            $status->value => ['label' => $status->label(), 'count' => (int) ($counts[$status->value] ?? 0)],
        ])->all();
    }

    /**
     * Conversion rate (won / total) per lead source — a simple ratio
     * over bounded, grouped aggregates, not a fragile HAVING clause.
     */
    protected function bySource(): \Illuminate\Support\Collection
    {
        $sources = LeadSource::query()->orderBy('name')->get();

        return $sources->map(function (LeadSource $source) {
            $total = $this->leadsQuery()->where('lead_source_id', $source->id)->count();
            $won = $this->leadsQuery()->where('lead_source_id', $source->id)->where('status', LeadStatus::Won->value)->count();

            return [
                'source' => $source->name,
                'total' => $total,
                'won' => $won,
                'conversion_rate' => $total > 0 ? round($won / $total * 100, 1) : null,
            ];
        });
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $bySource = $this->bySource();

        return response()->streamDownload(function () use ($bySource) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Source', 'Total Leads', 'Won', 'Conversion Rate %']);

            foreach ($bySource as $row) {
                fputcsv($out, [$row['source'], $row['total'], $row['won'], $row['conversion_rate']]);
            }

            fclose($out);
        }, 'sales-funnel-report.csv');
    }

    public function render()
    {
        return view('livewire.reports.sales-funnel', [
            'byStatus' => $this->byStatus(),
            'bySource' => $this->bySource(),
        ]);
    }
}
