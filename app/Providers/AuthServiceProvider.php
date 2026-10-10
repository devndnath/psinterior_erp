<?php

namespace App\Providers;

use App\Auth\ScopedEloquentUserProvider;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Boq;
use App\Models\ChangeOrder;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Design;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\GoodsReceive;
use App\Models\Handover;
use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Leave;
use App\Models\MaterialRequisition;
use App\Models\Meeting;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\ServiceRequest;
use App\Models\SiteVisit;
use App\Models\Snag;
use App\Models\StockTransfer;
use App\Models\Subcontractor;
use App\Models\SubcontractorAssignment;
use App\Models\SubcontractorPayment;
use App\Models\Unit;
use App\Models\Vendor;
use App\Models\VendorQuotation;
use App\Models\Warehouse;
use App\Models\Warranty;
use App\Policies\ActivityLogPolicy;
use App\Policies\AttendancePolicy;
use App\Policies\BoqPolicy;
use App\Policies\ChangeOrderPolicy;
use App\Policies\ClientPolicy;
use App\Policies\ContractPolicy;
use App\Policies\DesignPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\ExpenseCategoryPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\GoodsReceivePolicy;
use App\Policies\HandoverPolicy;
use App\Policies\InspectionPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\LeadPolicy;
use App\Policies\LeavePolicy;
use App\Policies\MaterialRequisitionPolicy;
use App\Policies\MeetingPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PayrollPolicy;
use App\Policies\ProductCategoryPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\PurchaseRequestPolicy;
use App\Policies\QuotationPolicy;
use App\Policies\ServiceRequestPolicy;
use App\Policies\SiteVisitPolicy;
use App\Policies\SnagPolicy;
use App\Policies\StockTransferPolicy;
use App\Policies\SubcontractorAssignmentPolicy;
use App\Policies\SubcontractorPaymentPolicy;
use App\Policies\SubcontractorPolicy;
use App\Policies\UnitPolicy;
use App\Policies\VendorPolicy;
use App\Policies\VendorQuotationPolicy;
use App\Policies\WarehousePolicy;
use App\Policies\WarrantyPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Model => Policy map. Every module adds its own model/policy pair
     * here as it's built (ARCHITECTURE.md §6/§61 — "use middleware/
     * policies/gates properly", never hard-code permission checks inside
     * controllers).
     */
    protected $policies = [
        ActivityLog::class => ActivityLogPolicy::class,
        Lead::class => LeadPolicy::class,
        Client::class => ClientPolicy::class,
        SiteVisit::class => SiteVisitPolicy::class,
        Project::class => ProjectPolicy::class,
        Design::class => DesignPolicy::class,
        Boq::class => BoqPolicy::class,
        Quotation::class => QuotationPolicy::class,
        Contract::class => ContractPolicy::class,
        ChangeOrder::class => ChangeOrderPolicy::class,
        ExpenseCategory::class => ExpenseCategoryPolicy::class,
        Invoice::class => InvoicePolicy::class,
        Payment::class => PaymentPolicy::class,
        Expense::class => ExpensePolicy::class,
        ProductCategory::class => ProductCategoryPolicy::class,
        Unit::class => UnitPolicy::class,
        Product::class => ProductPolicy::class,
        Warehouse::class => WarehousePolicy::class,
        StockTransfer::class => StockTransferPolicy::class,
        MaterialRequisition::class => MaterialRequisitionPolicy::class,
        Vendor::class => VendorPolicy::class,
        PurchaseRequest::class => PurchaseRequestPolicy::class,
        VendorQuotation::class => VendorQuotationPolicy::class,
        PurchaseOrder::class => PurchaseOrderPolicy::class,
        GoodsReceive::class => GoodsReceivePolicy::class,
        Employee::class => EmployeePolicy::class,
        Attendance::class => AttendancePolicy::class,
        Leave::class => LeavePolicy::class,
        Payroll::class => PayrollPolicy::class,
        Subcontractor::class => SubcontractorPolicy::class,
        SubcontractorAssignment::class => SubcontractorAssignmentPolicy::class,
        SubcontractorPayment::class => SubcontractorPaymentPolicy::class,
        Meeting::class => MeetingPolicy::class,
        Inspection::class => InspectionPolicy::class,
        Snag::class => SnagPolicy::class,
        Handover::class => HandoverPolicy::class,
        Warranty::class => WarrantyPolicy::class,
        ServiceRequest::class => ServiceRequestPolicy::class,
    ];

    public function register(): void {}

    public function boot(): void
    {
        $this->registerPolicies();

        // Backs config/auth.php's `scoped_eloquent` provider driver —
        // guarantees the `web`, `client` and `vendor` guards can only ever
        // authenticate a `users` row of their own user_type.
        Auth::provider('scoped_eloquent', function ($app, array $config) {
            return new ScopedEloquentUserProvider(
                $app['hash'],
                $config['model'],
                $config['user_type'],
            );
        });

        // Super Admin bypasses every permission check outright, matching
        // spec §3 ("Do NOT hard-code permissions inside controllers" — this
        // is the one deliberate, centrally-declared exception).
        Gate::before(function ($user, string $ability) {
            return $user->isStaff() && $user->hasRole('super-admin') ? true : null;
        });
    }
}
