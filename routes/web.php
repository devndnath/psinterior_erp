<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Boqs\Form as BoqForm;
use App\Livewire\Boqs\Index as BoqIndex;
use App\Livewire\Boqs\Show as BoqShow;
use App\Livewire\ChangeOrders\Form as ChangeOrderForm;
use App\Livewire\ChangeOrders\Index as ChangeOrderIndex;
use App\Livewire\ChangeOrders\Show as ChangeOrderShow;
use App\Livewire\Clients\Form as ClientForm;
use App\Livewire\Clients\Index as ClientIndex;
use App\Livewire\Clients\Show as ClientShow;
use App\Livewire\Contracts\Form as ContractForm;
use App\Livewire\Contracts\Index as ContractIndex;
use App\Livewire\Contracts\Show as ContractShow;
use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\Designs\Form as DesignForm;
use App\Livewire\Designs\Index as DesignIndex;
use App\Livewire\Designs\Show as DesignShow;
use App\Livewire\ExpenseCategories\Form as ExpenseCategoryForm;
use App\Livewire\ExpenseCategories\Index as ExpenseCategoryIndex;
use App\Livewire\Expenses\Form as ExpenseForm;
use App\Livewire\Expenses\Index as ExpenseIndex;
use App\Livewire\Expenses\Show as ExpenseShow;
use App\Livewire\GoodsReceives\Form as GoodsReceiveForm;
use App\Livewire\GoodsReceives\Show as GoodsReceiveShow;
use App\Livewire\Invoices\Form as InvoiceForm;
use App\Livewire\Invoices\Index as InvoiceIndex;
use App\Livewire\Invoices\Show as InvoiceShow;
use App\Livewire\Leads\Form as LeadForm;
use App\Livewire\Leads\Index as LeadIndex;
use App\Livewire\Leads\Show as LeadShow;
use App\Livewire\MaterialRequisitions\Form as MaterialRequisitionForm;
use App\Livewire\MaterialRequisitions\Index as MaterialRequisitionIndex;
use App\Livewire\MaterialRequisitions\Show as MaterialRequisitionShow;
use App\Livewire\Payments\Form as PaymentForm;
use App\Livewire\Payments\Index as PaymentIndex;
use App\Livewire\Payments\Show as PaymentShow;
use App\Livewire\ProductCategories\Form as ProductCategoryForm;
use App\Livewire\ProductCategories\Index as ProductCategoryIndex;
use App\Livewire\Products\Form as ProductForm;
use App\Livewire\Products\Index as ProductIndex;
use App\Livewire\Products\Show as ProductShow;
use App\Livewire\Profitability\Index as ProfitabilityIndex;
use App\Livewire\Projects\Form as ProjectForm;
use App\Livewire\Projects\Index as ProjectIndex;
use App\Livewire\Projects\Show as ProjectShow;
use App\Livewire\PurchaseOrders\Form as PurchaseOrderForm;
use App\Livewire\PurchaseOrders\Index as PurchaseOrderIndex;
use App\Livewire\PurchaseOrders\Show as PurchaseOrderShow;
use App\Livewire\PurchaseRequests\Form as PurchaseRequestForm;
use App\Livewire\PurchaseRequests\Index as PurchaseRequestIndex;
use App\Livewire\PurchaseRequests\Show as PurchaseRequestShow;
use App\Livewire\Quotations\Form as QuotationForm;
use App\Livewire\Quotations\Index as QuotationIndex;
use App\Livewire\Quotations\Show as QuotationShow;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Livewire\SiteVisits\Form as SiteVisitForm;
use App\Livewire\SiteVisits\Index as SiteVisitIndex;
use App\Livewire\SiteVisits\Show as SiteVisitShow;
use App\Livewire\StockMovements\Index as StockMovementIndex;
use App\Livewire\StockTransfers\Form as StockTransferForm;
use App\Livewire\StockTransfers\Index as StockTransferIndex;
use App\Livewire\StockTransfers\Show as StockTransferShow;
use App\Livewire\Units\Form as UnitForm;
use App\Livewire\Units\Index as UnitIndex;
use App\Livewire\VendorQuotations\Form as VendorQuotationForm;
use App\Livewire\Vendors\Form as VendorForm;
use App\Livewire\Vendors\Index as VendorIndex;
use App\Livewire\Vendors\Show as VendorShow;
use App\Livewire\Warehouses\Form as WarehouseForm;
use App\Livewire\Warehouses\Index as WarehouseIndex;
use App\Livewire\Warehouses\Show as WarehouseShow;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Staff ("web" guard) routes
|--------------------------------------------------------------------------
|
| Every module built from Phase 2 onward registers its routes here (or in
| its own routes/<module>.php required from this file, once the route list
| grows large enough to split). Client and vendor portals are entirely
| separate files — see bootstrap/app.php.
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});

Route::post('/logout', LogoutController::class)->name('logout')->defaults('guard', 'web');

Route::middleware(['auth:web', 'user_type:staff'])->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');

    Route::middleware('can:manage_settings')->group(function () {
        Route::get('/settings', SettingsIndex::class)->name('settings.index');
    });

    // Phase 2: CRM — Leads, Clients, Site Visits (ARCHITECTURE.md §6/§7).
    Route::prefix('leads')->name('leads.')->group(function () {
        Route::get('/', LeadIndex::class)->name('index')->middleware('can:view_leads');
        Route::get('/create', LeadForm::class)->name('create')->middleware('can:create_leads');
        Route::get('/{lead}/edit', LeadForm::class)->name('edit')->middleware('can:edit_leads');
        Route::get('/{lead}', LeadShow::class)->name('show')->middleware('can:view_leads');
    });

    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', ClientIndex::class)->name('index')->middleware('can:view_clients');
        Route::get('/create', ClientForm::class)->name('create')->middleware('can:create_clients');
        Route::get('/{client}/edit', ClientForm::class)->name('edit')->middleware('can:edit_clients');
        Route::get('/{client}', ClientShow::class)->name('show')->middleware('can:view_clients');
    });

    Route::prefix('site-visits')->name('site-visits.')->group(function () {
        Route::get('/', SiteVisitIndex::class)->name('index')->middleware('can:view_site_visits');
        Route::get('/create', SiteVisitForm::class)->name('create')->middleware('can:create_site_visits');
        Route::get('/{siteVisit}/edit', SiteVisitForm::class)->name('edit')->middleware('can:edit_site_visits');
        Route::get('/{siteVisit}', SiteVisitShow::class)->name('show')->middleware('can:view_site_visits');
    });

    // Phase 3: Projects, Milestones, Tasks (ARCHITECTURE.md §6.3).
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', ProjectIndex::class)->name('index')->middleware('can:view_projects');
        Route::get('/create', ProjectForm::class)->name('create')->middleware('can:create_projects');
        Route::get('/{project}/edit', ProjectForm::class)->name('edit')->middleware('can:edit_projects');
        Route::get('/{project}', ProjectShow::class)->name('show')->middleware('can:view_projects');
    });

    // Phase 4: Design — revisions and client approvals (ARCHITECTURE.md §3.5).
    Route::prefix('designs')->name('designs.')->group(function () {
        Route::get('/', DesignIndex::class)->name('index')->middleware('can:view_designs');
        Route::get('/create', DesignForm::class)->name('create')->middleware('can:create_designs');
        Route::get('/{design}/edit', DesignForm::class)->name('edit')->middleware('can:edit_designs');
        Route::get('/{design}', DesignShow::class)->name('show')->middleware('can:view_designs');
    });

    // Phase 5: BOQ -> Quotation -> Contract -> Change Order (ARCHITECTURE.md §3.6).
    Route::prefix('boqs')->name('boqs.')->group(function () {
        Route::get('/', BoqIndex::class)->name('index')->middleware('can:view_boqs');
        Route::get('/create', BoqForm::class)->name('create')->middleware('can:create_boqs');
        Route::get('/{boq}/edit', BoqForm::class)->name('edit')->middleware('can:edit_boqs');
        Route::get('/{boq}', BoqShow::class)->name('show')->middleware('can:view_boqs');
    });

    Route::prefix('quotations')->name('quotations.')->group(function () {
        Route::get('/', QuotationIndex::class)->name('index')->middleware('can:view_quotations');
        Route::get('/create', QuotationForm::class)->name('create')->middleware('can:create_quotations');
        Route::get('/{quotation}/edit', QuotationForm::class)->name('edit')->middleware('can:edit_quotations');
        Route::get('/{quotation}', QuotationShow::class)->name('show')->middleware('can:view_quotations');
    });

    Route::prefix('contracts')->name('contracts.')->group(function () {
        Route::get('/', ContractIndex::class)->name('index')->middleware('can:view_contracts');
        Route::get('/create', ContractForm::class)->name('create')->middleware('can:create_contracts');
        Route::get('/{contract}/edit', ContractForm::class)->name('edit')->middleware('can:edit_contracts');
        Route::get('/{contract}', ContractShow::class)->name('show')->middleware('can:view_contracts');
    });

    Route::prefix('change-orders')->name('change-orders.')->group(function () {
        Route::get('/', ChangeOrderIndex::class)->name('index')->middleware('can:view_change_orders');
        Route::get('/create', ChangeOrderForm::class)->name('create')->middleware('can:create_change_orders');
        Route::get('/{changeOrder}/edit', ChangeOrderForm::class)->name('edit')->middleware('can:edit_change_orders');
        Route::get('/{changeOrder}', ChangeOrderShow::class)->name('show')->middleware('can:view_change_orders');
    });

    // Phase 6: Invoices, Payments, Expenses, Profitability (ARCHITECTURE.md §3.7).
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', InvoiceIndex::class)->name('index')->middleware('can:view_invoices');
        Route::get('/create', InvoiceForm::class)->name('create')->middleware('can:create_invoices');
        Route::get('/{invoice}/edit', InvoiceForm::class)->name('edit')->middleware('can:edit_invoices');
        Route::get('/{invoice}', InvoiceShow::class)->name('show')->middleware('can:view_invoices');
    });

    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', PaymentIndex::class)->name('index')->middleware('can:view_payments');
        Route::get('/create', PaymentForm::class)->name('create')->middleware('can:create_payments');
        Route::get('/{payment}', PaymentShow::class)->name('show')->middleware('can:view_payments');
    });

    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/', ExpenseIndex::class)->name('index')->middleware('can:view_expenses');
        Route::get('/create', ExpenseForm::class)->name('create')->middleware('can:create_expenses');
        Route::get('/{expense}/edit', ExpenseForm::class)->name('edit')->middleware('can:edit_expenses');
        Route::get('/{expense}', ExpenseShow::class)->name('show')->middleware('can:view_expenses');
    });

    Route::prefix('expense-categories')->name('expense-categories.')->group(function () {
        Route::get('/', ExpenseCategoryIndex::class)->name('index')->middleware('can:manage_settings');
        Route::get('/create', ExpenseCategoryForm::class)->name('create')->middleware('can:manage_settings');
        Route::get('/{expenseCategory}/edit', ExpenseCategoryForm::class)->name('edit')->middleware('can:manage_settings');
    });

    Route::get('/profitability', ProfitabilityIndex::class)->name('profitability.index')->middleware('can:view_finance');

    // Phase 7: Inventory, Warehouses, Stock, Material Requisitions
    // (ARCHITECTURE.md §3.8, scoped to Inventory/Warehouse/MR only —
    // Vendors/PO/GRN are Phase 8).
    Route::prefix('product-categories')->name('product-categories.')->group(function () {
        Route::get('/', ProductCategoryIndex::class)->name('index')->middleware('can:manage_settings');
        Route::get('/create', ProductCategoryForm::class)->name('create')->middleware('can:manage_settings');
        Route::get('/{productCategory}/edit', ProductCategoryForm::class)->name('edit')->middleware('can:manage_settings');
    });

    Route::prefix('units')->name('units.')->group(function () {
        Route::get('/', UnitIndex::class)->name('index')->middleware('can:manage_settings');
        Route::get('/create', UnitForm::class)->name('create')->middleware('can:manage_settings');
        Route::get('/{unit}/edit', UnitForm::class)->name('edit')->middleware('can:manage_settings');
    });

    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', ProductIndex::class)->name('index')->middleware('can:view_inventory');
        Route::get('/create', ProductForm::class)->name('create')->middleware('can:create_inventory');
        Route::get('/{product}/edit', ProductForm::class)->name('edit')->middleware('can:edit_inventory');
        Route::get('/{product}', ProductShow::class)->name('show')->middleware('can:view_inventory');
    });

    Route::prefix('warehouses')->name('warehouses.')->group(function () {
        Route::get('/', WarehouseIndex::class)->name('index')->middleware('can:view_warehouses');
        Route::get('/create', WarehouseForm::class)->name('create')->middleware('can:create_warehouses');
        Route::get('/{warehouse}/edit', WarehouseForm::class)->name('edit')->middleware('can:edit_warehouses');
        Route::get('/{warehouse}', WarehouseShow::class)->name('show')->middleware('can:view_warehouses');
    });

    Route::get('/stock-movements', StockMovementIndex::class)->name('stock-movements.index')->middleware('can:view_inventory');

    // No edit route — a transfer is never edited once created, only
    // shipped/completed/cancelled via Livewire component methods on its
    // Show page (no delete either — see StockTransferPolicy).
    Route::prefix('stock-transfers')->name('stock-transfers.')->group(function () {
        Route::get('/', StockTransferIndex::class)->name('index')->middleware('can:view_warehouses');
        Route::get('/create', StockTransferForm::class)->name('create')->middleware('can:create_warehouses');
        Route::get('/{stockTransfer}', StockTransferShow::class)->name('show')->middleware('can:view_warehouses');
    });

    Route::prefix('material-requisitions')->name('material-requisitions.')->group(function () {
        Route::get('/', MaterialRequisitionIndex::class)->name('index')->middleware('can:view_material_requisitions');
        Route::get('/create', MaterialRequisitionForm::class)->name('create')->middleware('can:create_material_requisitions');
        Route::get('/{materialRequisition}/edit', MaterialRequisitionForm::class)->name('edit')->middleware('can:edit_material_requisitions');
        Route::get('/{materialRequisition}', MaterialRequisitionShow::class)->name('show')->middleware('can:view_material_requisitions');
    });

    // Phase 8: Vendors, Procurement (Purchase Requests -> Vendor
    // Quotations -> Purchase Orders), and Goods Receives
    // (ARCHITECTURE.md §3.8). Purchase Requests and Vendor Quotations
    // are gated on the existing `*_purchase_orders` abilities, NOT on
    // invented `purchase_requests`/`vendor_quotations` permission
    // strings — see PurchaseRequestPolicy's docblock for why.
    Route::prefix('vendors')->name('vendors.')->group(function () {
        Route::get('/', VendorIndex::class)->name('index')->middleware('can:view_vendors');
        Route::get('/create', VendorForm::class)->name('create')->middleware('can:create_vendors');
        Route::get('/{vendor}/edit', VendorForm::class)->name('edit')->middleware('can:edit_vendors');
        Route::get('/{vendor}', VendorShow::class)->name('show')->middleware('can:view_vendors');
    });

    Route::prefix('purchase-requests')->name('purchase-requests.')->group(function () {
        Route::get('/', PurchaseRequestIndex::class)->name('index')->middleware('can:view_purchase_orders');
        Route::get('/create', PurchaseRequestForm::class)->name('create')->middleware('can:create_purchase_orders');
        Route::get('/{purchaseRequest}/edit', PurchaseRequestForm::class)->name('edit')->middleware('can:edit_purchase_orders');
        Route::get('/{purchaseRequest}', PurchaseRequestShow::class)->name('show')->middleware('can:view_purchase_orders');
    });

    // No Index/Show — a vendor quotation is always viewed in the
    // context of its parent Purchase Request's Show page.
    Route::get('/vendor-quotations/create', VendorQuotationForm::class)->name('vendor-quotations.create')->middleware('can:create_purchase_orders');

    Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
        Route::get('/', PurchaseOrderIndex::class)->name('index')->middleware('can:view_purchase_orders');
        Route::get('/create', PurchaseOrderForm::class)->name('create')->middleware('can:create_purchase_orders');
        Route::get('/{purchaseOrder}/edit', PurchaseOrderForm::class)->name('edit')->middleware('can:edit_purchase_orders');
        Route::get('/{purchaseOrder}', PurchaseOrderShow::class)->name('show')->middleware('can:view_purchase_orders');
    });

    // No Index — always reached from a PO's Show page. No edit/delete —
    // a GRN is an immutable receiving record once created.
    Route::get('/goods-receives/create', GoodsReceiveForm::class)->name('goods-receives.create')->middleware('can:create_goods_receives');
    Route::get('/goods-receives/{goodsReceive}', GoodsReceiveShow::class)->name('goods-receives.show')->middleware('can:view_goods_receives');
});
