<?php

namespace App\Livewire\Dashboard;

use App\Enums\ChangeOrderStatus;
use App\Enums\DesignStatus;
use App\Enums\InvoiceStatus;
use App\Enums\LeadStatus;
use App\Enums\LeaveApprovalStatus;
use App\Enums\MaterialRequisitionStatus;
use App\Enums\PayrollStatus;
use App\Enums\ProjectStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\QuotationStatus;
use App\Enums\ServiceRequestStatus;
use App\Enums\SnagStatus;
use App\Models\ChangeOrder;
use App\Models\Client;
use App\Models\Design;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Leave;
use App\Models\MaterialRequisition;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\ServiceRequest;
use App\Models\SiteVisit;
use App\Models\Snag;
use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Role-aware dashboard. Phase 1 shipped an empty shell; Phase 2 added the
 * CRM KPI cards; Phase 3 added Projects; Phase 4 added Designs awaiting a
 * client decision; Phase 5 adds Quotations awaiting a decision and
 * pending Change Orders (spec §4); Phase 6 adds Outstanding Invoices and
 * This Month's Expenses; Phase 7 adds Low Stock Products and Pending
 * Material Requisitions; Phase 8 adds Pending Purchase Orders; Phase 9
 * adds Pending Leave Requests and Payroll Due; Phase 10 adds Open Snags
 * and Open Service Requests (the latter gated on `view_warranties` per
 * ServiceRequestPolicy's permission-reuse decision, not an invented
 * `view_service_requests`). Each card
 * gated by the same permission its own module uses so a user without
 * `view_leads` simply doesn't see that card.
 */
#[Layout('layouts.admin', ['title' => 'Dashboard'])]
#[Title('Dashboard')]
class Index extends Component
{
    /** Statuses whose invoices still carry an unpaid balance. */
    private const OPEN_INVOICE_STATUSES = [
        InvoiceStatus::Issued,
        InvoiceStatus::PartiallyPaid,
        InvoiceStatus::Overdue,
    ];

    /**
     * Brand-layout pass: the finance overview behind the dashboard's top
     * half (rings, net-cash card, ratio bars, month-over-month KPI cards,
     * 6-month cash-flow chart, largest open invoices). Every figure is
     * computed from real Payment / Expense / Invoice / Project rows — no
     * placeholder or estimated numbers; where a comparison has no data
     * (e.g. nothing recorded last month) the delta is null and the view
     * says so instead of showing 0% or a fake trend.
     *
     * Each block is gated on the same permission as its own module
     * (view_payments / view_expenses / view_invoices / view_projects);
     * composite figures (margin, net cash) appear only when the user can
     * see BOTH inputs. Date bucketing (daily / monthly) is done in PHP
     * over bounded windows (14 days / 6 months / year-to-date) rather than
     * DB-specific DATE_FORMAT/strftime SQL, so it runs the same on MySQL
     * and SQLite.
     */
    private function finance($user, ?Collection $openInvoices): array
    {
        $today = CarbonImmutable::today();
        $monthStart = $today->startOfMonth();
        $prevStart = $monthStart->subMonthNoOverflow();
        $prevEnd = $monthStart->subDay();
        $yearStart = $today->startOfYear();
        $sparkStart = $today->subDays(13);
        $chartStart = $monthStart->subMonthsNoOverflow(5);

        $canPay = $user->can('view_payments');
        $canExp = $user->can('view_expenses');
        $canInv = $user->can('view_invoices');

        $sumBetween = fn ($query, string $col, string $amountCol, $from, $to) => (float) $query
            ->whereBetween($col, [$from->toDateString(), $to->toDateString()])
            ->sum($amountCol);

        $toDate = fn ($v) => $v instanceof \DateTimeInterface ? CarbonImmutable::instance($v) : CarbonImmutable::parse($v);

        // Daily buckets for the last 14 days, oldest first.
        $daily = function (Collection $rows, string $dateKey, string $amountKey) use ($sparkStart, $toDate): array {
            $byDay = $rows->groupBy(fn ($r) => $toDate($r->{$dateKey})->toDateString())
                ->map(fn ($g) => (float) $g->sum($amountKey));

            return collect(range(0, 13))->map(function ($i) use ($sparkStart, $byDay) {
                $d = $sparkStart->addDays($i);

                return ['label' => $d->format('d M'), 'value' => round((float) ($byDay[$d->toDateString()] ?? 0), 2)];
            })->all();
        };

        // Monthly buckets for the last 6 months (incl. current), oldest first.
        $monthly = function (Collection $rows, string $dateKey, string $amountKey) use ($chartStart, $toDate): array {
            $byMonth = $rows->groupBy(fn ($r) => $toDate($r->{$dateKey})->format('Y-m'))
                ->map(fn ($g) => (float) $g->sum($amountKey));

            return collect(range(0, 5))->map(fn ($i) => round((float) ($byMonth[$chartStart->addMonthsNoOverflow($i)->format('Y-m')] ?? 0), 2))->all();
        };

        $delta = fn (?float $cur, ?float $prev) => ($cur === null || $prev === null || $prev <= 0)
            ? null
            : round(($cur - $prev) / $prev * 100, 1);

        $income = null;
        if ($canPay) {
            $cur = $sumBetween(Payment::query(), 'date', 'amount', $monthStart, $today->endOfMonth());
            $prev = $sumBetween(Payment::query(), 'date', 'amount', $prevStart, $prevEnd);
            $rows = Payment::query()->where('date', '>=', $chartStart->toDateString())->get(['date', 'amount']);
            $income = [
                'current' => $cur,
                'previous' => $prev,
                'delta' => $delta($cur, $prev),
                'spark' => $daily($rows->filter(fn ($r) => $toDate($r->date)->gte($sparkStart)), 'date', 'amount'),
                'monthly' => $monthly($rows, 'date', 'amount'),
                'ytd' => $sumBetween(Payment::query(), 'date', 'amount', $yearStart, $today),
            ];
        }

        $expenses = null;
        if ($canExp) {
            $cur = $sumBetween(Expense::query(), 'date', 'amount', $monthStart, $today->endOfMonth());
            $prev = $sumBetween(Expense::query(), 'date', 'amount', $prevStart, $prevEnd);
            $rows = Expense::query()->where('date', '>=', $chartStart->toDateString())->get(['date', 'amount']);
            $expenses = [
                'current' => $cur,
                'previous' => $prev,
                'delta' => $delta($cur, $prev),
                'spark' => $daily($rows->filter(fn ($r) => $toDate($r->date)->gte($sparkStart)), 'date', 'amount'),
                'monthly' => $monthly($rows, 'date', 'amount'),
                'ytd' => $sumBetween(Expense::query(), 'date', 'amount', $yearStart, $today),
            ];
        }

        $invoiced = null;
        $receivables = null;
        $ratios = null;
        $topOpenInvoices = null;
        if ($canInv) {
            $live = fn () => Invoice::query()->where('status', '!=', InvoiceStatus::Cancelled->value);
            $cur = $sumBetween($live(), 'invoice_date', 'grand_total', $monthStart, $today->endOfMonth());
            $prev = $sumBetween($live(), 'invoice_date', 'grand_total', $prevStart, $prevEnd);
            $rows = $live()->where('invoice_date', '>=', $sparkStart->toDateString())->get(['invoice_date', 'grand_total']);
            $invoiced = [
                'current' => $cur,
                'previous' => $prev,
                'delta' => $delta($cur, $prev),
                'spark' => $daily($rows, 'invoice_date', 'grand_total'),
            ];

            $open = $openInvoices ?? collect();
            $outstanding = (float) $open->sum(fn (Invoice $i) => $i->outstandingAmount());
            $overdue = $open->filter(fn (Invoice $i) => $i->due_date && $i->due_date->lt($today));
            $overdueAmount = (float) $overdue->sum(fn (Invoice $i) => $i->outstandingAmount());
            $receivables = [
                'total' => $outstanding,
                'count' => $open->count(),
                'overdueCount' => $overdue->count(),
                'overdueAmount' => $overdueAmount,
            ];

            $billed = (float) $live()->sum('grand_total');
            $collected = (float) $live()->sum('paid_amount');
            $ratios = [
                // Share of everything ever billed that has been collected.
                'collection' => $billed > 0 ? round($collected / $billed * 100, 1) : null,
                // Share of the open receivable balance that is past due.
                'overdueShare' => $outstanding > 0 ? round($overdueAmount / $outstanding * 100, 1) : null,
            ];

            $topOpenInvoices = $open
                ->sortByDesc(fn (Invoice $i) => $i->outstandingAmount())
                ->take(5)
                ->values();
        }

        $margin = null;
        $netCash = null;
        if ($income && $expenses) {
            $netCash = $income['ytd'] - $expenses['ytd'];
            $margin = $income['ytd'] > 0 ? round($netCash / $income['ytd'] * 100, 1) : null;
        }

        return [
            'currency' => Setting::get('finance.currency', 'BDT'),
            'monthLabel' => $today->format('F Y'),
            'yearLabel' => $today->format('Y'),
            'chartMonths' => collect(range(0, 5))->map(fn ($i) => $chartStart->addMonthsNoOverflow($i)->format('M'))->all(),
            'income' => $income,
            'expenses' => $expenses,
            'invoiced' => $invoiced,
            'receivables' => $receivables,
            'ratios' => $ratios,
            'topOpenInvoices' => $topOpenInvoices,
            'margin' => $margin,
            'netCash' => $netCash,
            'avgProjectProgress' => $user->can('view_projects')
                ? (($avg = Project::whereNotIn('status', [ProjectStatus::Completed->value, ProjectStatus::Cancelled->value])->avg('progress')) === null ? null : round((float) $avg, 1))
                : null,
        ];
    }

    public function render()
    {
        $user = Auth::user();

        // Fetched once and shared by the outstanding-invoices KPI and the
        // finance overview (receivables, overdue share, largest open).
        $openInvoices = $user->can('view_invoices')
            ? Invoice::with('client:id,name')
                ->whereIn('status', array_map(fn ($s) => $s->value, self::OPEN_INVOICE_STATUSES))
                ->get()
            : null;

        return view('livewire.dashboard.index', $this->finance($user, $openInvoices) + [
            'totalLeads' => $user->can('view_leads') ? Lead::count() : null,
            'newLeads' => $user->can('view_leads') ? Lead::where('status', LeadStatus::New)->count() : null,
            'openLeads' => $user->can('view_leads') ? Lead::whereIn('status', array_filter(
                LeadStatus::cases(),
                fn (LeadStatus $s) => $s->isOpen(),
            ))->count() : null,
            'totalClients' => $user->can('view_clients') ? Client::count() : null,
            'upcomingSiteVisits' => $user->can('view_site_visits')
                ? SiteVisit::where('visit_date', '>=', now()->toDateString())->count()
                : null,
            'activeProjects' => $user->can('view_projects') ? Project::whereNotIn('status', [
                ProjectStatus::Completed->value,
                ProjectStatus::Cancelled->value,
            ])->count() : null,
            'projectsOnHold' => $user->can('view_projects') ? Project::where('status', ProjectStatus::OnHold->value)->count() : null,
            'designsAwaitingDecision' => $user->can('view_designs') ? Design::whereIn('status', [
                DesignStatus::Submitted->value,
                DesignStatus::UnderReview->value,
            ])->count() : null,
            'quotationsAwaitingDecision' => $user->can('view_quotations') ? Quotation::whereIn('status', [
                QuotationStatus::Sent->value,
                QuotationStatus::Viewed->value,
                QuotationStatus::Negotiation->value,
            ])->count() : null,
            'changeOrdersPending' => $user->can('view_change_orders') ? ChangeOrder::where('status', ChangeOrderStatus::Pending->value)->count() : null,
            'outstandingInvoicesCount' => $openInvoices?->count(),
            'outstandingInvoicesTotal' => $openInvoices?->sum(fn (Invoice $invoice) => $invoice->outstandingAmount()),
            'expensesThisMonth' => $user->can('view_expenses') ? (float) Expense::whereBetween('date', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])->sum('amount') : null,
            // Subquery WHERE, not HAVING on a withSum() alias without
            // GROUP BY — that fails under MySQL's default
            // ONLY_FULL_GROUP_BY mode (error 1463, hit in production).
            'lowStockProducts' => $user->can('view_inventory') ? Product::query()
                ->whereRaw(
                    '(select coalesce(sum(ws.quantity), 0) from warehouse_stocks ws where ws.product_id = products.id) < products.min_stock'
                )
                ->count() : null,
            'pendingRequisitions' => $user->can('view_material_requisitions') ? MaterialRequisition::whereIn('status', [
                MaterialRequisitionStatus::Pending->value,
                MaterialRequisitionStatus::ManagerApproved->value,
                MaterialRequisitionStatus::StoreApproved->value,
            ])->count() : null,
            'pendingPurchaseOrders' => $user->can('view_purchase_orders') ? PurchaseOrder::whereIn('status', [
                PurchaseOrderStatus::Draft->value,
                PurchaseOrderStatus::Sent->value,
                PurchaseOrderStatus::PartiallyReceived->value,
            ])->count() : null,
            // Pending at either stage — a leave only drops off this
            // count once BOTH manager_status and hr_status have moved
            // past Pending (approved or rejected).
            'pendingLeaveRequests' => $user->can('view_leaves') ? Leave::where(function ($q) {
                $q->where('manager_status', LeaveApprovalStatus::Pending->value)
                    ->orWhere('hr_status', LeaveApprovalStatus::Pending->value);
            })->count() : null,
            'payrollDue' => $user->can('view_payroll') ? Payroll::where('month', now()->format('Y-m'))
                ->whereIn('status', [PayrollStatus::Draft->value, PayrollStatus::Finalized->value])
                ->count() : null,
            'openSnags' => $user->can('view_snags') ? Snag::where('status', '!=', SnagStatus::Closed->value)->count() : null,
            // Gated on view_warranties, not an invented
            // view_service_requests — see ServiceRequestPolicy.
            'openServiceRequests' => $user->can('view_warranties') ? ServiceRequest::where('status', '!=', ServiceRequestStatus::Closed->value)->count() : null,
            // Phase 15a: lead-status breakdown for the Dashboard's new
            // Chart.js bar chart. Aggregated via GROUP BY status (status
            // is in the GROUP BY clause, so this is safe under MySQL's
            // default ONLY_FULL_GROUP_BY — no raw HAVING on a
            // non-aggregated column). Same view_leads gate as every
            // other lead stat above.
            //
            // Not ->pluck('total', 'status'): ->get() hydrates Eloquent
            // models, so 'status' is cast to the LeadStatus backed enum
            // (see Lead::casts()). An enum object as an array key throws
            // "Illegal offset type" (Illuminate\Support\Arr::pluck).
            // Unwrap ->value explicitly instead — same bug class fixed in
            // Reports\Hr::attendanceByEmployee() and Reports\SalesFunnel::byStatus().
            'leadsByStatus' => $user->can('view_leads') ? Lead::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->get()
                ->mapWithKeys(fn ($row) => [
                    $row->status instanceof \BackedEnum ? $row->status->value : $row->status => $row->total,
                ])
                ->all() : null,
            // Phase: branding/dashboard redesign — two more breakdown
            // charts, same safe GROUP BY + BackedEnum-unwrap pattern as
            // leadsByStatus above, gated on the same permission as each
            // metric's own existing KPI card.
            'projectsByStatus' => $user->can('view_projects') ? Project::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->get()
                ->mapWithKeys(fn ($row) => [
                    $row->status instanceof \BackedEnum ? $row->status->value : $row->status => $row->total,
                ])
                ->all() : null,
            'invoicesByStatus' => $user->can('view_invoices') ? Invoice::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->get()
                ->mapWithKeys(fn ($row) => [
                    $row->status instanceof \BackedEnum ? $row->status->value : $row->status => $row->total,
                ])
                ->all() : null,
        ]);
    }
}
