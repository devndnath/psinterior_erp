<?php

namespace App\Livewire\Crm;

use App\Enums\FollowupStatus;
use App\Enums\FollowupType;
use App\Enums\InvoiceStatus;
use App\Enums\LeadStatus;
use App\Enums\ProjectStatus;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\SiteVisit;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * CRM overview — what the CRM card on the dashboard opens: calls to
 * make, client visits and meetings coming up, the lead pipeline, project
 * stages, what clients owe, and new leads/clients by month this year.
 * The layout of the firm's previous system's CRM dashboard, in the
 * soft-canvas style.
 *
 * "Calls" are pending Call/WhatsApp follow-ups on open leads; a pending
 * follow-up's `date` is when it's due (LeadFollowup::isOverdue() uses the
 * same rule). Each section is null when the user can't see its module.
 */
#[Layout('layouts.admin', ['title' => 'CRM Overview'])]
#[Title('CRM Overview')]
class Overview extends Component
{
    public function mount(): void
    {
        abort_unless(Auth::user()?->can('view_leads'), 403);
    }

    /** Today / overdue / next 7 days / next 30 days, for a dated query. */
    private function buckets($query, string $col, CarbonImmutable $today, bool $withOverdue): array
    {
        $d = fn ($v) => $v->toDateString();

        return array_filter([
            'today' => (clone $query)->whereDate($col, $d($today))->count(),
            'overdue' => $withOverdue ? (clone $query)->whereDate($col, '<', $d($today))->count() : null,
            'week' => (clone $query)->whereDate($col, '>', $d($today))->whereDate($col, '<=', $d($today->addDays(7)))->count(),
            'month' => (clone $query)->whereDate($col, '>', $d($today))->whereDate($col, '<=', $d($today->addDays(30)))->count(),
        ], fn ($v) => $v !== null);
    }

    public function render()
    {
        $user = Auth::user();
        $today = CarbonImmutable::today();
        $openStatuses = array_map(fn (LeadStatus $s) => $s->value, array_filter(LeadStatus::cases(), fn (LeadStatus $s) => $s->isOpen()));

        // Calls -------------------------------------------------------
        $callQuery = LeadFollowup::query()
            ->where('status', FollowupStatus::Pending->value)
            ->whereIn('type', [FollowupType::Call->value, FollowupType::WhatsApp->value])
            ->whereHas('lead', fn ($q) => $q->whereIn('status', $openStatuses));

        $calls = [
            'counts' => $this->buckets($callQuery, 'date', $today, true),
            // Overdue and today's first, oldest first — the call list.
            'due' => (clone $callQuery)->whereDate('date', '<=', $today->toDateString())
                ->with(['lead:id,lead_no,name,phone,company_name', 'assignee:id,name'])
                ->orderBy('date')->limit(8)->get(),
        ];

        // Client visits -------------------------------------------------
        $visits = null;
        if ($user->can('view_site_visits')) {
            $visits = [
                'counts' => $this->buckets(SiteVisit::query(), 'visit_date', $today, false),
                'next' => SiteVisit::query()->whereDate('visit_date', '>=', $today->toDateString())
                    ->with(['client:id,name', 'assignee:id,name'])
                    ->orderBy('visit_date')->limit(6)->get(),
            ];
        }

        // Meetings --------------------------------------------------------
        $meetings = null;
        if ($user->can('view_meetings')) {
            $meetings = [
                'counts' => $this->buckets(Meeting::query(), 'date', $today, false),
                'next' => Meeting::query()->whereDate('date', '>=', $today->toDateString())
                    ->with('project:id,project_code,name')
                    ->orderBy('date')->orderBy('time')->limit(6)->get(),
            ];
        }

        // Lead pipeline -------------------------------------------------
        $byStatus = Lead::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');
        $pipeline = collect(LeadStatus::cases())->map(fn (LeadStatus $s) => [
            'label' => $s->label(),
            'count' => (int) ($byStatus[$s->value] ?? 0),
        ])->all();
        $monthStart = $today->startOfMonth()->toDateString();
        $leads = [
            'open' => Lead::whereIn('status', $openStatuses)->count(),
            'newThisMonth' => Lead::whereDate('created_at', '>=', $monthStart)->count(),
            'wonThisMonth' => Lead::where('status', LeadStatus::Won->value)->whereDate('converted_at', '>=', $monthStart)->count(),
            'winRate' => ($closed = (int) (($byStatus[LeadStatus::Won->value] ?? 0) + ($byStatus[LeadStatus::Lost->value] ?? 0))) > 0
                ? round(($byStatus[LeadStatus::Won->value] ?? 0) / $closed * 100) : null,
        ];

        // Projects ------------------------------------------------------
        $projects = null;
        if ($user->can('view_projects')) {
            $pByStatus = Project::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');
            $projects = [
                'active' => Project::whereNotIn('status', [ProjectStatus::Completed->value, ProjectStatus::Cancelled->value])->count(),
                'rows' => collect(ProjectStatus::cases())->map(fn (ProjectStatus $s) => [
                    'label' => $s->label(), 'count' => (int) ($pByStatus[$s->value] ?? 0),
                ])->all(),
            ];
        }

        // Client dues -----------------------------------------------------
        $dues = null;
        if ($user->can('view_invoices')) {
            $open = Invoice::query()
                ->whereIn('status', [InvoiceStatus::Issued->value, InvoiceStatus::PartiallyPaid->value, InvoiceStatus::Overdue->value])
                ->with('client:id,name')
                ->get(['id', 'invoice_no', 'client_id', 'due_date', 'grand_total', 'paid_amount', 'status']);
            $overdue = $open->filter(fn (Invoice $i) => $i->due_date && $i->due_date->lt($today));
            $dues = [
                'total' => (float) $open->sum(fn (Invoice $i) => $i->outstandingAmount()),
                'overdue' => (float) $overdue->sum(fn (Invoice $i) => $i->outstandingAmount()),
                'overdueCount' => $overdue->count(),
                'top' => $open->sortByDesc(fn (Invoice $i) => $i->outstandingAmount())->take(5)->values(),
            ];
        }

        // New leads / clients by month, this year --------------------------
        $yearStart = $today->startOfYear();
        $byMonth = fn ($rows) => collect(range(1, 12))->map(fn ($m) => $rows->filter(fn ($d) => (int) CarbonImmutable::parse($d)->month === $m)->count())->all();
        $chart = [
            'labels' => collect(range(1, 12))->map(fn ($m) => $yearStart->month($m)->format('M'))->all(),
            'leads' => $byMonth(Lead::whereDate('created_at', '>=', $yearStart->toDateString())->pluck('created_at')),
            'clients' => $user->can('view_clients')
                ? $byMonth(Client::whereDate('created_at', '>=', $yearStart->toDateString())->pluck('created_at'))
                : null,
        ];

        return view('livewire.crm.overview', compact('calls', 'visits', 'meetings', 'pipeline', 'leads', 'projects', 'dues', 'chart') + [
            'currency' => \App\Models\Setting::get('finance.currency', 'BDT'),
            'today' => $today,
            'year' => $today->year,
        ]);
    }
}
