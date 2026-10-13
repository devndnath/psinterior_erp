<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Client;
use App\Models\Department;
use App\Models\Designation;
use App\Models\ExpenseCategory;
use App\Models\LeadSource;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\Vendor;
use App\Models\Warehouse;
use App\Services\AttendanceService;
use App\Services\BoqService;
use App\Services\ChangeOrderService;
use App\Services\ClientService;
use App\Services\ContractService;
use App\Services\DesignService;
use App\Services\EmployeeService;
use App\Services\ExpenseService;
use App\Services\GoodsReceiveService;
use App\Services\HandoverService;
use App\Services\InspectionService;
use App\Services\InventoryService;
use App\Services\InvoiceService;
use App\Services\LeadService;
use App\Services\LeaveService;
use App\Services\MaterialRequisitionService;
use App\Services\MeetingService;
use App\Services\PaymentScheduleService;
use App\Services\PaymentService;
use App\Services\PayrollService;
use App\Services\ProjectService;
use App\Services\PurchaseOrderService;
use App\Services\PurchaseRequestService;
use App\Services\QuotationService;
use App\Services\SelectionBoardService;
use App\Services\ServiceRequestService;
use App\Services\SiteReportService;
use App\Services\SiteVisitService;
use App\Services\SnagService;
use App\Services\SubcontractorAssignmentService;
use App\Services\SubcontractorPaymentService;
use App\Services\SubcontractorService;
use App\Services\UserService;
use App\Services\VendorInvoiceService;
use App\Services\VendorQuotationService;
use App\Services\VendorService;
use App\Services\WarrantyService;
use Illuminate\Database\Seeder;

/**
 * Demo dataset (README.md §5 / spec §65) — one interlinked sample record
 * per module, from Lead all the way to Handover/Warranty/Service Request,
 * so a fresh install looks like a real firm's data on first login rather
 * than an empty shell. Built entirely through the same Service classes
 * the UI calls (never raw Eloquent for anything a Service owns), so
 * numbering, totals, stock movements and status rules are exercised
 * exactly as they would be by a human using the app.
 *
 * Deliberately NOT seeded here: `activity_logs`, `notifications`,
 * `documents` and `document_sequences` — these are byproducts of real
 * user actions (LogsActivity, Notification::send, FileService uploads,
 * NumberingService's first call) and populate themselves the moment any
 * of the records below are created or a real file is uploaded through
 * the UI. Faking rows directly into them would not exercise anything and
 * documents specifically would reference files that don't exist on disk.
 *
 * Re-running `php artisan db:seed --class=DemoSeeder` is safe: it checks
 * for its own marker client (by email) first and does nothing if found,
 * rather than duplicating a second copy of the whole dataset.
 */
class DemoSeeder extends Seeder
{
    protected UserService $users;

    protected EmployeeService $employees;

    public function run(): void
    {
        if (Client::where('email', 'contact@greenview-residences.test')->exists()) {
            $this->command?->warn('DemoSeeder: demo dataset already present — skipping (delete the "Greenview Residences" client to force a re-seed).');

            return;
        }

        // Foundation seeders this dataset depends on — idempotent
        // (firstOrCreate/syncPermissions throughout), safe to call even
        // if DatabaseSeeder already ran them.
        $this->call([
            CompanySeeder::class,
            SettingsSeeder::class,
            RolePermissionSeeder::class,
            SuperAdminSeeder::class,
            LeadSourceSeeder::class,
        ]);

        $this->users = app(UserService::class);
        $this->employees = app(EmployeeService::class);

        [$departments, $designations] = $this->seedFoundation();
        $staff = $this->seedStaffAndEmployees($departments, $designations);
        $crm = $this->seedCrm($staff);
        $projects = $this->seedProjects($staff, $crm);
        $this->seedDesign($projects['flagship']);
        $sales = $this->seedSalesDocuments($projects['flagship'], $staff);
        $this->seedFinance($projects['flagship'], $sales['contract']);
        $inventory = $this->seedInventory();
        $vendor = $this->seedProcurement($projects['flagship'], $inventory, $staff);
        $this->seedHr($staff['employees']);
        $this->seedSubcontracting($projects['flagship']);
        $this->seedMeetingsAndQc($projects['flagship'], $staff);
        $this->seedHandoverWarranty($projects['flagship'], $crm['flagshipClient']);
        $this->seedSelectionBoardAndReports($projects['midflight'], $staff);

        $this->command?->info('DemoSeeder: demo dataset created. Generated portal/staff passwords were printed above where applicable (plain "password" for named staff logins).');
    }

    // -----------------------------------------------------------------
    // Foundation
    // -----------------------------------------------------------------

    /** @return array{0: array<string,Department>, 1: array<string,Designation>} */
    protected function seedFoundation(): array
    {
        $departments = [
            'design' => Department::create(['name' => 'Design', 'code' => 'DSN']),
            'site' => Department::create(['name' => 'Site Operations', 'code' => 'OPS']),
            'procurement' => Department::create(['name' => 'Procurement', 'code' => 'PRC']),
            'accounts' => Department::create(['name' => 'Accounts', 'code' => 'ACC']),
            'hr' => Department::create(['name' => 'Human Resources', 'code' => 'HR']),
        ];

        $designations = [
            'designer' => Designation::create(['department_id' => $departments['design']->id, 'name' => 'Interior Designer']),
            'site_engineer' => Designation::create(['department_id' => $departments['site']->id, 'name' => 'Site Engineer']),
            'procurement_officer' => Designation::create(['department_id' => $departments['procurement']->id, 'name' => 'Procurement Officer']),
            'accountant' => Designation::create(['department_id' => $departments['accounts']->id, 'name' => 'Accountant']),
            'hr_manager' => Designation::create(['department_id' => $departments['hr']->id, 'name' => 'HR Manager']),
        ];

        return [$departments, $designations];
    }

    /** @return array{users: array<string,\App\Models\User>, employees: array<string,\App\Models\Employee>} */
    protected function seedStaffAndEmployees(array $departments, array $designations): array
    {
        $roster = [
            'pm' => ['name' => 'Rafiq Ahmed', 'email' => 'rafiq.pm@demo.test', 'role' => 'project-manager', 'dept' => 'site', 'des' => 'site_engineer'],
            'designer' => ['name' => 'Nadia Islam', 'email' => 'nadia.designer@demo.test', 'role' => 'designer', 'dept' => 'design', 'des' => 'designer'],
            'site_engineer' => ['name' => 'Tanvir Hasan', 'email' => 'tanvir.site@demo.test', 'role' => 'site-engineer', 'dept' => 'site', 'des' => 'site_engineer'],
            'accountant' => ['name' => 'Farhana Kabir', 'email' => 'farhana.accounts@demo.test', 'role' => 'accountant', 'dept' => 'accounts', 'des' => 'accountant'],
            'procurement_officer' => ['name' => 'Imran Chowdhury', 'email' => 'imran.procurement@demo.test', 'role' => 'procurement-officer', 'dept' => 'procurement', 'des' => 'procurement_officer'],
            'store_manager' => ['name' => 'Shahin Mia', 'email' => 'shahin.store@demo.test', 'role' => 'store-manager', 'dept' => 'procurement', 'des' => 'procurement_officer'],
            'hr_manager' => ['name' => 'Sumaiya Rahman', 'email' => 'sumaiya.hr@demo.test', 'role' => 'hr-manager', 'dept' => 'hr', 'des' => 'hr_manager'],
        ];

        $users = [];
        $employees = [];

        foreach ($roster as $key => $person) {
            $user = $this->users->create([
                'name' => $person['name'],
                'email' => $person['email'],
                'phone' => '+8801'.random_int(600000000, 999999999),
                'password' => 'password',
                'is_active' => true,
            ], [$person['role']]);

            $users[$key] = $user;

            $employees[$key] = $this->employees->create([
                'user_id' => $user->id,
                'name' => $person['name'],
                'email' => $person['email'],
                'phone' => $user->phone,
                'department_id' => $departments[$person['dept']]->id,
                'designation_id' => $designations[$person['des']]->id,
                'joining_date' => now()->subMonths(random_int(6, 36))->toDateString(),
                'salary' => random_int(35000, 95000),
                'employment_type' => 'full_time',
                'status' => 'active',
            ]);
        }

        // One junior employee with no login at all — most real staff
        // (helpers, junior site staff) never get a system account.
        $employees['junior_designer'] = $this->employees->create([
            'name' => 'Mim Akter',
            'phone' => '+8801'.random_int(600000000, 999999999),
            'department_id' => $departments['design']->id,
            'designation_id' => $designations['designer']->id,
            'joining_date' => now()->subMonths(4)->toDateString(),
            'salary' => 28000,
            'employment_type' => 'full_time',
            'status' => 'active',
        ]);

        $this->command?->info('Demo staff logins (password for all: "password"): '.implode(', ', array_map(fn ($p) => $p['email'], $roster)));

        return ['users' => $users, 'employees' => $employees];
    }

    // -----------------------------------------------------------------
    // CRM: Clients, Leads, Site Visits
    // -----------------------------------------------------------------

    protected function seedCrm(array $staff): array
    {
        $clientsSvc = app(ClientService::class);
        $leadsSvc = app(LeadService::class);
        $siteVisitSvc = app(SiteVisitService::class);

        $flagshipClient = $clientsSvc->create([
            'name' => 'Greenview Residences Ltd.',
            'type' => 'company',
            'phone' => '+8801711000111',
            'email' => 'contact@greenview-residences.test',
            'company_name' => 'Greenview Residences Ltd.',
            'address' => 'House 12, Road 5, Gulshan, Dhaka',
            'billing_address' => 'House 12, Road 5, Gulshan, Dhaka',
            'site_address' => 'Plot 45, Bashundhara R/A, Dhaka',
            'assigned_to' => $staff['users']['pm']->id,
            'notes' => 'Flagship demo client — full residential fit-out, taken end-to-end through handover and warranty.',
        ]);

        $clientsSvc->addContact($flagshipClient, [
            'name' => 'Kamal Hossain',
            'designation' => 'Facilities Director',
            'phone' => '+8801711000222',
            'email' => 'kamal@greenview-residences.test',
            'is_primary' => true,
        ]);

        // Portal login for the flagship client — same flow as the
        // "give this client portal access" checkbox on Clients\Form.
        ['user' => $portalUser, 'password' => $portalPassword] = $this->users->createLinkedAccount(
            $flagshipClient->name,
            $flagshipClient->email,
            UserType::Client,
        );
        $flagshipClient->update(['user_id' => $portalUser->id]);
        $this->command?->info("Demo client portal login: {$portalUser->email} / {$portalPassword}");

        $cafeClient = $clientsSvc->create([
            'name' => 'Nova Café & Bistro',
            'type' => 'company',
            'company_name' => 'Nova Hospitality Ltd.',
            'phone' => '+8801811444555',
            'email' => 'ops@nova-cafe.test',
            'address' => 'Level 3, Jamuna Future Park, Dhaka',
            'assigned_to' => $staff['users']['pm']->id,
        ]);

        $prospect = $clientsSvc->create([
            'name' => 'Farhan Sarker',
            'type' => 'individual',
            'phone' => '+8801911222333',
            'email' => 'farhan.sarker@demo.test',
            'address' => 'Road 11, Banani, Dhaka',
        ]);

        $source = LeadSource::first();

        $wonLead = $leadsSvc->create([
            'name' => 'Mehnaz Tabassum',
            'phone' => '+8801611777888',
            'email' => 'mehnaz.t@demo.test',
            'project_type' => 'residential',
            'estimated_budget' => 1500000,
            'interested_service' => 'Full Interior',
            'lead_source_id' => $source?->id,
            'assigned_to' => $staff['users']['pm']->id,
            'priority' => 'high',
            'expected_closing_date' => now()->addDays(20)->toDateString(),
        ]);

        $leadsSvc->addFollowup($wonLead, [
            'date' => now()->subDays(5)->toDateString(),
            'type' => 'call',
            'notes' => 'Initial discovery call — discussed budget and timeline.',
            'next_followup_date' => now()->addDays(2)->toDateString(),
            'assigned_to' => $staff['users']['pm']->id,
        ]);

        $wonLead->update(['status' => 'won']);
        $leadClient = $leadsSvc->convertToClient($wonLead, ['assigned_to' => $staff['users']['pm']->id]);

        $lostLead = $leadsSvc->create([
            'name' => 'Office Space Dhaka',
            'phone' => '+8801511999000',
            'project_type' => 'office',
            'estimated_budget' => 800000,
            'interested_service' => 'Office Fit-out',
            'lead_source_id' => $source?->id,
            'priority' => 'medium',
            'expected_closing_date' => now()->subDays(5)->toDateString(),
            'notes' => 'Went with a competitor over timeline.',
        ]);
        $lostLead->update(['status' => 'lost']);

        $siteVisitSvc->create([
            'client_id' => $prospect->id,
            'visit_date' => now()->subDays(10)->toDateString(),
            'assigned_to' => $staff['users']['site_engineer']->id,
            'purpose' => 'Initial measurement',
            'site_address' => $prospect->address,
        ], [
            ['area_name' => 'Living Room', 'length' => 18, 'width' => 14, 'height' => 10, 'notes' => 'Open plan, natural light from north wall.'],
            ['area_name' => 'Master Bedroom', 'length' => 14, 'width' => 12, 'height' => 10, 'notes' => null],
        ]);

        return [
            'flagshipClient' => $flagshipClient,
            'cafeClient' => $cafeClient,
            'prospect' => $prospect,
            'leadClient' => $leadClient,
            'wonLead' => $wonLead,
        ];
    }

    // -----------------------------------------------------------------
    // Projects
    // -----------------------------------------------------------------

    protected function seedProjects(array $staff, array $crm): array
    {
        $projectsSvc = app(ProjectService::class);

        $flagship = $projectsSvc->create([
            'client_id' => $crm['flagshipClient']->id,
            'name' => 'Greenview Residences — Full Interior Fit-out',
            'type' => 'residential',
            'location' => 'Bashundhara R/A, Dhaka',
            'start_date' => now()->subMonths(4)->toDateString(),
            'expected_end_date' => now()->addMonths(1)->toDateString(),
            'actual_end_date' => now()->subDays(3)->toDateString(),
            'estimated_budget' => 4500000,
            'project_manager_id' => $staff['users']['pm']->id,
            'designer_id' => $staff['users']['designer']->id,
            'site_engineer_id' => $staff['users']['site_engineer']->id,
            'priority' => 'high',
            'status' => 'completed',
            'description' => 'Full 3-bedroom residential fit-out — flagship demo project taken through to handover and warranty.',
        ]);
        $flagship->update(['progress' => 100]);

        $projectsSvc->addMilestone($flagship, [
            'name' => 'Design Sign-off', 'start_date' => now()->subMonths(4)->toDateString(),
            'due_date' => now()->subMonths(3)->toDateString(), 'assigned_to' => $staff['users']['designer']->id,
            'status' => 'completed', 'progress' => 100, 'priority' => 'high',
        ]);
        $projectsSvc->addMilestone($flagship, [
            'name' => 'Civil & Carpentry', 'start_date' => now()->subMonths(3)->toDateString(),
            'due_date' => now()->subMonth()->toDateString(), 'assigned_to' => $staff['users']['site_engineer']->id,
            'status' => 'completed', 'progress' => 100, 'priority' => 'high',
        ]);
        $projectsSvc->addTask($flagship, [
            'name' => 'Final snag-free walkthrough', 'assigned_to' => $staff['users']['site_engineer']->id,
            'due_date' => now()->subDays(4)->toDateString(), 'status' => 'completed', 'progress' => 100, 'priority' => 'high',
        ]);
        $projectsSvc->addMember($flagship, $staff['users']['pm']->id, 'Project Manager');
        $projectsSvc->addMember($flagship, $staff['users']['designer']->id, 'Lead Designer');
        $projectsSvc->addMember($flagship, $staff['users']['site_engineer']->id, 'Site Engineer');

        $midflight = $projectsSvc->create([
            'client_id' => $crm['leadClient']->id,
            'lead_id' => $crm['wonLead']->id,
            'name' => 'Mehnaz Residence — Full Interior',
            'type' => 'residential',
            'location' => 'Dhanmondi, Dhaka',
            'start_date' => now()->subMonth()->toDateString(),
            'expected_end_date' => now()->addMonths(3)->toDateString(),
            'estimated_budget' => 1500000,
            'project_manager_id' => $staff['users']['pm']->id,
            'designer_id' => $staff['users']['designer']->id,
            'priority' => 'high',
            'status' => 'execution',
        ]);
        $projectsSvc->addMilestone($midflight, [
            'name' => 'BOQ & Quotation', 'due_date' => now()->addDays(10)->toDateString(),
            'status' => 'in_progress', 'progress' => 40, 'priority' => 'medium',
        ]);
        $projectsSvc->addTask($midflight, [
            'name' => 'Site measurement verification', 'assigned_to' => $staff['users']['site_engineer']->id,
            'due_date' => now()->addDays(5)->toDateString(), 'status' => 'in_progress', 'progress' => 50, 'priority' => 'medium',
        ]);

        $planning = $projectsSvc->create([
            'client_id' => $crm['cafeClient']->id,
            'name' => 'Nova Café — Dine-in Refurbishment',
            'type' => 'restaurant',
            'location' => 'Jamuna Future Park, Dhaka',
            'estimated_budget' => 900000,
            'project_manager_id' => $staff['users']['pm']->id,
            'priority' => 'medium',
            'status' => 'planning',
        ]);

        return ['flagship' => $flagship, 'midflight' => $midflight, 'planning' => $planning];
    }

    // -----------------------------------------------------------------
    // Design
    // -----------------------------------------------------------------

    protected function seedDesign($project): void
    {
        $designSvc = app(DesignService::class);

        $design = $designSvc->create([
            'project_id' => $project->id,
            'title' => 'Living Room Layout',
            'category' => 'floor_plan',
            'description' => 'Open-plan living/dining layout with built-in storage wall.',
            'status' => 'draft',
        ]);

        // Revision created directly (no UploadedFile in a seeder context,
        // same reasoning DesignRevisionFactory already uses in tests —
        // file_document_id is nullable precisely for this case).
        $revision = $design->revisions()->create([
            'version' => 1,
            'description' => 'First pass — open-plan concept.',
            'status' => 'approved',
        ]);

        $revision->approvals()->create([
            'client_id' => $project->client_id,
            'decision' => 'approved',
            'comment' => 'Looks great, please proceed.',
            'decided_at' => now()->subMonths(3),
        ]);

        $design->update(['status' => 'approved', 'client_approval_status' => 'approved']);
    }

    // -----------------------------------------------------------------
    // BOQ, Quotation, Contract, Change Order, Payment Schedule
    // -----------------------------------------------------------------

    protected function seedSalesDocuments($project, array $staff): array
    {
        $boqSvc = app(BoqService::class);
        $quotationSvc = app(QuotationService::class);
        $contractSvc = app(ContractService::class);
        $changeOrderSvc = app(ChangeOrderService::class);
        $scheduleSvc = app(PaymentScheduleService::class);

        $boq = $boqSvc->create($project, ['status' => 'active'], [
            ['item' => 'False Ceiling', 'description' => 'Gypsum false ceiling, living & dining', 'category' => 'Carpentry', 'qty' => 450, 'unit' => 'sqft', 'rate' => 180, 'labour_cost' => 30000, 'material_cost' => 51000],
            ['item' => 'Wardrobe', 'description' => 'Built-in wardrobe, 3 bedrooms', 'category' => 'Carpentry', 'qty' => 3, 'unit' => 'unit', 'rate' => 85000],
            ['item' => 'Wall Paint', 'description' => 'Premium emulsion, full apartment', 'category' => 'Finishing', 'qty' => 2200, 'unit' => 'sqft', 'rate' => 65],
        ]);

        $quotation = $quotationSvc->create($project, ['discount' => 20000, 'tax' => 0, 'terms' => '50% advance, 30% mid-term, 20% on handover.'], [], $boq);
        $quotationSvc->changeStatus($quotation, 'sent');
        $quotationSvc->changeStatus($quotation, 'approved');

        $contract = $contractSvc->createFromQuotation($quotation, [
            'start_date' => now()->subMonths(4)->toDateString(),
            'end_date' => now()->subDays(3)->toDateString(),
            'payment_terms' => '50/30/20 milestone-based',
            'warranty_terms' => '12 months on workmanship, per manufacturer terms on fittings.',
            'status' => 'active',
        ]);

        $scheduleSvc->addLine($contract, ['label' => 'Advance (50%)', 'percentage' => 50, 'due_date' => now()->subMonths(4)->toDateString()]);
        $scheduleSvc->addLine($contract, ['label' => 'Mid-term (30%)', 'percentage' => 30, 'due_date' => now()->subMonths(2)->toDateString()]);
        $scheduleSvc->addLine($contract, ['label' => 'On Handover (20%)', 'percentage' => 20, 'due_date' => now()->subDays(3)->toDateString()]);

        $changeOrder = $changeOrderSvc->create($project, ['description' => 'Upgrade to imported hardware for all wardrobes.'], [
            ['description' => 'Soft-close hinges & channels upgrade', 'qty' => 3, 'rate' => 15000],
        ]);
        $changeOrderSvc->recordClientDecision($changeOrder, 'approved');

        return ['boq' => $boq, 'quotation' => $quotation, 'contract' => $contract, 'changeOrder' => $changeOrder];
    }

    // -----------------------------------------------------------------
    // Finance: Invoice, Payment, Expense
    // -----------------------------------------------------------------

    protected function seedFinance($project, $contract): void
    {
        $invoiceSvc = app(InvoiceService::class);
        $paymentSvc = app(PaymentService::class);
        $expenseSvc = app(ExpenseService::class);

        $invoice = $invoiceSvc->create($project, [
            'contract_id' => $contract->id,
            'type' => 'progress',
            'invoice_date' => now()->subMonths(2)->toDateString(),
            'due_date' => now()->subMonth()->toDateString(),
        ], [
            ['description' => 'Mid-term progress billing (30%)', 'qty' => 1, 'rate' => (float) $contract->amount * 0.3],
        ]);
        $invoiceSvc->issue($invoice);

        $paymentSvc->create([
            'client_id' => $project->client_id,
            'project_id' => $project->id,
            'invoice_id' => $invoice->id,
            'date' => now()->subMonth()->toDateString(),
            'amount' => (float) $invoice->grand_total,
            'method' => 'bank',
            'reference' => 'TXN-DEMO-0001',
            'account' => 'Main Operating Account',
        ]);

        $category = ExpenseCategory::firstOrCreate(['name' => 'Site Materials'], ['is_active' => true]);

        $expenseSvc->create([
            'project_id' => $project->id,
            'category_id' => $category->id,
            'amount' => 42000,
            'date' => now()->subMonths(2)->toDateString(),
            'method' => 'cash',
            'description' => 'Cement, sand and misc. site consumables.',
        ]);
    }

    // -----------------------------------------------------------------
    // Inventory
    // -----------------------------------------------------------------

    protected function seedInventory(): array
    {
        $inventorySvc = app(InventoryService::class);

        $category = ProductCategory::create(['name' => 'Hardware & Fittings', 'is_active' => true]);
        $unit = Unit::create(['name' => 'Piece', 'short_code' => 'PCS']);

        $hinges = Product::create([
            'sku' => 'SKU-HNG-001', 'name' => 'Soft-Close Hinge (pair)', 'category_id' => $category->id,
            'brand' => 'Hettich', 'unit_id' => $unit->id, 'purchase_price' => 450, 'usage_rate' => 2, 'min_stock' => 50,
            'description' => 'Standard soft-close cabinet hinge pair.',
        ]);
        $plywood = Product::create([
            'sku' => 'SKU-PLY-001', 'name' => '18mm Marine Plywood Sheet', 'category_id' => $category->id,
            'brand' => 'Local', 'unit_id' => $unit->id, 'purchase_price' => 3200, 'usage_rate' => 1, 'min_stock' => 20,
            'description' => '18mm marine-grade plywood, 8x4 ft sheet.',
        ]);

        $mainWarehouse = Warehouse::create(['name' => 'Main Warehouse', 'type' => 'main', 'location' => 'Tejgaon, Dhaka']);
        $siteWarehouse = Warehouse::create(['name' => 'Greenview Site Store', 'type' => 'site', 'location' => 'Bashundhara R/A, Dhaka']);

        $inventorySvc->openingStock($hinges, $mainWarehouse, 200);
        $inventorySvc->openingStock($plywood, $mainWarehouse, 60);
        $inventorySvc->openingStock($plywood, $siteWarehouse, 10);

        return ['category' => $category, 'unit' => $unit, 'hinges' => $hinges, 'plywood' => $plywood, 'mainWarehouse' => $mainWarehouse, 'siteWarehouse' => $siteWarehouse];
    }

    // -----------------------------------------------------------------
    // Procurement: Vendor, Material Requisition, PR -> VQ -> PO -> GRN, Vendor Invoice
    // -----------------------------------------------------------------

    protected function seedProcurement($project, array $inventory, array $staff): Vendor
    {
        $vendorSvc = app(VendorService::class);
        $mrSvc = app(MaterialRequisitionService::class);
        $prSvc = app(PurchaseRequestService::class);
        $vqSvc = app(VendorQuotationService::class);
        $poSvc = app(PurchaseOrderService::class);
        $grnSvc = app(GoodsReceiveService::class);
        $viSvc = app(VendorInvoiceService::class);

        $vendor = $vendorSvc->create([
            'company_name' => 'Apex Hardware Supplies',
            'contact_person' => 'Rashed Khan',
            'phone' => '+8801911555666',
            'email' => 'sales@apex-hardware.test',
            'address' => 'Bangshal, Dhaka',
            'category' => 'Hardware',
            'tax_info' => 'TIN-123456789',
        ]);

        ['user' => $vendorPortalUser, 'password' => $vendorPassword] = $this->users->createLinkedAccount(
            $vendor->contact_person,
            $vendor->email,
            UserType::Vendor,
        );
        $vendor->update(['user_id' => $vendorPortalUser->id]);
        $this->command?->info("Demo vendor portal login: {$vendorPortalUser->email} / {$vendorPassword}");

        $vendor->products()->attach([
            $inventory['hinges']->id => ['vendor_price' => 420],
            $inventory['plywood']->id => ['vendor_price' => 3050],
        ]);

        // Material Requisition — taken through its full Pending ->
        // ManagerApproved -> StoreApproved -> Issued chain so the demo
        // includes a real stock-decreasing movement, not just a draft row.
        $mr = $mrSvc->create($project, [
            'requested_by' => $staff['users']['site_engineer']->id,
            'warehouse_id' => $inventory['mainWarehouse']->id,
            'date' => now()->subMonths(2)->toDateString(),
            'purpose' => 'Hinges for wardrobe installation',
        ], [
            ['product_id' => $inventory['hinges']->id, 'quantity' => 12],
        ]);
        $mrSvc->approveByManager($mr);
        $mrSvc->approveByStore($mr);
        $mrSvc->issue($mr, $inventory['mainWarehouse']->id, $staff['users']['store_manager']->id);

        // Full procurement chain: PR -> VQ -> select winner -> PO -> GRN.
        $pr = $prSvc->create($project, ['requested_by' => $staff['users']['procurement_officer']->id], [
            ['product_id' => $inventory['plywood']->id, 'quantity' => 15],
        ]);
        $prSvc->sendForQuotation($pr);

        $vq = $vqSvc->create($pr, ['vendor_id' => $vendor->id], [
            ['product_id' => $inventory['plywood']->id, 'qty' => 15, 'rate' => 3050],
        ]);
        $vqSvc->selectWinner($vq);

        $po = $poSvc->createFromVendorQuotation($vq, [
            'warehouse_id' => $inventory['mainWarehouse']->id,
            'date' => now()->subMonth()->toDateString(),
            'expected_delivery' => now()->subDays(20)->toDateString(),
        ]);
        $poSvc->send($po);

        $grnSvc->create($po, [
            'received_date' => now()->subDays(18)->toDateString(),
            'received_by' => $staff['users']['store_manager']->id,
        ], [
            ['purchase_order_item_id' => $po->items->first()->id, 'received_qty' => 15, 'damaged_qty' => 0],
        ]);

        // Vendor-submitted invoice against that same PO, approved (which
        // posts the matching payable Expense automatically).
        $vi = $viSvc->submit($po->fresh(), $vendor, [
            'vendor_invoice_no' => 'APEX-INV-0042',
            'invoice_date' => now()->subDays(15)->toDateString(),
            'amount' => 15 * 3050,
        ]);
        $viSvc->approve($vi, [
            'category_id' => ExpenseCategory::firstOrCreate(['name' => 'Vendor Payments'], ['is_active' => true])->id,
            'date' => now()->subDays(10)->toDateString(),
            'method' => 'bank',
        ], $staff['users']['procurement_officer']->id);

        return $vendor;
    }

    // -----------------------------------------------------------------
    // HR: Attendance, Leave, Payroll
    // -----------------------------------------------------------------

    protected function seedHr(array $employees): void
    {
        $attendanceSvc = app(AttendanceService::class);
        $leaveSvc = app(LeaveService::class);
        $payrollSvc = app(PayrollService::class);

        foreach (['pm', 'designer', 'site_engineer'] as $key) {
            $employee = $employees[$key];

            for ($i = 1; $i <= 5; $i++) {
                $date = now()->subDays($i)->toDateString();
                $attendanceSvc->create([
                    'employee_id' => $employee->id,
                    'date' => $date,
                    'check_in' => "{$date} 09:05:00",
                    'check_out' => "{$date} 18:10:00",
                    'status' => 'present',
                ]);
            }

            $payrollSvc->generate($employee, now()->subMonth()->format('Y-m'), [
                'basic' => (float) $employee->salary * 0.6,
                'allowance' => (float) $employee->salary * 0.4,
                'overtime' => 0,
                'bonus' => 0,
                'deduction' => 0,
                'advance' => 0,
            ]);
        }

        $leave = $leaveSvc->create($employees['designer'], [
            'type' => 'casual',
            'from_date' => now()->addDays(10)->toDateString(),
            'to_date' => now()->addDays(12)->toDateString(),
            'reason' => 'Family event out of town.',
        ]);
        $leaveSvc->approveByManager($leave);
        $leaveSvc->approveByHr($leave);
    }

    // -----------------------------------------------------------------
    // Subcontracting
    // -----------------------------------------------------------------

    protected function seedSubcontracting($project): void
    {
        $subSvc = app(SubcontractorService::class);
        $assignmentSvc = app(SubcontractorAssignmentService::class);
        $subPaymentSvc = app(SubcontractorPaymentService::class);

        $subcontractor = $subSvc->create([
            'name' => 'Bashar Electric Works',
            'trade' => 'electrician',
            'phone' => '+8801711888999',
            'rate_type' => 'lump sum',
        ]);

        $assignment = $assignmentSvc->create($project, [
            'subcontractor_id' => $subcontractor->id,
            'work_description' => 'Full electrical wiring and fixture installation.',
            'rate' => 180000,
            'progress' => 100,
        ]);

        $subPaymentSvc->create($assignment, [
            'date' => now()->subMonth()->toDateString(),
            'amount' => 180000,
            'notes' => 'Final payment on completion.',
        ]);
    }

    // -----------------------------------------------------------------
    // Meetings, Inspections, Snags
    // -----------------------------------------------------------------

    protected function seedMeetingsAndQc($project, array $staff): void
    {
        $meetingSvc = app(MeetingService::class);
        $inspectionSvc = app(InspectionService::class);
        $snagSvc = app(SnagService::class);

        $meetingSvc->create([
            'project_id' => $project->id,
            'type' => 'site',
            'date' => now()->subDays(6)->toDateString(),
            'time' => '11:00',
            'location' => 'Project site',
            'agenda' => 'Pre-handover walkthrough planning.',
            'discussion' => 'Reviewed snag list and finishing touches.',
            'decisions' => 'Final walkthrough scheduled for this week.',
        ], [
            ['type' => 'user', 'id' => $staff['users']['pm']->id],
            ['type' => 'user', 'id' => $staff['users']['site_engineer']->id],
        ]);

        $inspection = $inspectionSvc->create($project, [
            'area' => 'Living Room',
            'inspection_date' => now()->subDays(5)->toDateString(),
            'inspector_id' => $staff['users']['site_engineer']->id,
        ], [
            ['checklist_item' => 'Paint finish — no visible brush marks', 'result' => 'passed'],
            ['checklist_item' => 'Wardrobe alignment and door gaps', 'result' => 'requires_correction', 'notes' => 'Left door of wardrobe #2 slightly misaligned.'],
        ]);

        $snag = $snagSvc->create($project, ['area' => 'Living Room', 'issue' => 'Wardrobe #2 left door misaligned, needs re-hinging.'], $inspection);
        $snagSvc->assign($snag, $staff['users']['site_engineer']->id);
        $snagSvc->startProgress($snag);
        $snagSvc->markFixed($snag);
        $snagSvc->verify($snag);
        $snagSvc->close($snag);
    }

    // -----------------------------------------------------------------
    // Handover, Warranty, Service Request
    // -----------------------------------------------------------------

    protected function seedHandoverWarranty($project, Client $client): void
    {
        $handoverSvc = app(HandoverService::class);
        $warrantySvc = app(WarrantyService::class);
        $serviceRequestSvc = app(ServiceRequestService::class);

        $handover = $handoverSvc->create($project, [
            'handover_date' => now()->subDays(3)->toDateString(),
            'notes' => 'Keys and final documentation handed over to the client.',
        ]);
        $handoverSvc->confirmByClient($handover);

        $warranty = $warrantySvc->create($project, [
            'start_date' => now()->subDays(3)->toDateString(),
            'end_date' => now()->addMonths(12)->toDateString(),
            'terms' => '12 months on workmanship; fittings per manufacturer warranty.',
        ]);

        $serviceRequestSvc->create($warranty, [
            'client_id' => $client->id,
            'complaint' => 'One kitchen cabinet drawer runner is sticking.',
        ]);
    }

    // -----------------------------------------------------------------
    // Selection Boards, Site Reports
    // -----------------------------------------------------------------

    protected function seedSelectionBoardAndReports($project, array $staff): void
    {
        $selectionSvc = app(SelectionBoardService::class);
        $siteReportSvc = app(SiteReportService::class);

        $board = $selectionSvc->create([
            'project_id' => $project->id,
            'area' => 'Kitchen',
            'title' => 'Countertop Material',
            'description' => 'Client to choose between three countertop finishes.',
        ], $staff['users']['designer']);

        $optionA = $selectionSvc->addOption($board, ['label' => 'Quartz — Calacatta White', 'price' => 4500, 'notes' => 'Most popular, low maintenance.']);
        $selectionSvc->addOption($board, ['label' => 'Granite — Black Galaxy', 'price' => 3200]);

        $selectionSvc->send($board);
        $selectionSvc->recordDecision($board, $optionA, 'We like the quartz option.');

        $siteReportSvc->create($project, [
            'reported_by' => $staff['users']['site_engineer']->id,
            'report_date' => now()->subDays(2)->toDateString(),
            'weather' => 'Sunny',
            'manpower_count' => 6,
            'work_summary' => 'Final finishing touches and deep cleaning ahead of handover.',
            'issues' => null,
        ]);
    }
}
