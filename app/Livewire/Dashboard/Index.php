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
use App\Models\Payroll;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\ServiceRequest;
use App\Models\SiteVisit;
use App\Models\Snag;
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
    public function render()
    {
        $user = Auth::user();

        return view('livewire.dashboard.index', [
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
            'outstandingInvoicesCount' => $user->can('view_invoices') ? Invoice::whereIn('status', [
                InvoiceStatus::Issued->value,
                InvoiceStatus::PartiallyPaid->value,
                InvoiceStatus::Overdue->value,
            ])->count() : null,
            'outstandingInvoicesTotal' => $user->can('view_invoices') ? Invoice::whereIn('status', [
                InvoiceStatus::Issued->value,
                InvoiceStatus::PartiallyPaid->value,
                InvoiceStatus::Overdue->value,
            ])->get()->sum(fn (Invoice $invoice) => $invoice->outstandingAmount()) : null,
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
        ]);
    }
}
