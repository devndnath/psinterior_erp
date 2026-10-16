<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\ExpenseCategory;
use App\Models\ExpenseClaim;
use App\Models\Invoice;
use App\Models\LeadSource;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Warehouse;
use App\Services\AttendanceService;
use App\Services\BoqService;
use App\Services\ChangeOrderService;
use App\Services\ClientService;
use App\Services\ContractService;
use App\Services\DesignService;
use App\Services\EmployeeService;
use App\Services\ExpenseClaimService;
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
use App\Services\StockTransferService;
use App\Services\SubcontractorAssignmentService;
use App\Services\SubcontractorPaymentService;
use App\Services\SubcontractorService;
use App\Services\VendorInvoiceService;
use App\Services\VendorQuotationService;
use App\Services\VendorService;
use App\Services\WarrantyService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Full sample dataset — realistic VOLUME data for every business table,
 * layered on top of DemoSeeder's single interlinked walkthrough.
 *
 *   php artisan db:seed --class=SampleDataSeeder
 *
 * DemoSeeder proves every module works end-to-end with one record each;
 * this seeder makes the app look like a firm that has been operating for
 * a year: ~20 clients, ~45 leads in every pipeline stage, 14 projects in
 * every status, ~35 invoices and their payments spread over 12 months,
 * ~150 expenses, 22 products across 3 warehouses (some below minimum
 * stock), 5 procurement cycles, 6 weeks of attendance for 16 employees,
 * leaves, conveyance claims, 3 months of payroll, subcontractors,
 * meetings, inspections, snags, site reports, handovers, warranties and
 * service requests. Dates are spread backwards from today so the
 * dashboard's month-over-month figures, sparklines, cash-flow chart and
 * invoice ageing all have real history to show.
 *
 * Like DemoSeeder, everything goes through the same Service classes the
 * UI uses (numbering, totals, stock movements, invoice payment status and
 * payroll calculations are all exercised for real). Tables that fill
 * themselves — activity_logs, notifications, document_sequences — are
 * populated as a side effect. Not seeded on purpose: `documents` (would
 * point at files that don't exist on disk) and framework tables (cache,
 * jobs, sessions, password_reset_tokens, failed_jobs).
 *
 * Random choices use a fixed seed, so every run on a fresh database
 * produces the same dataset. Safe to re-run: skips itself if its marker
 * client already exists. Staff logins created here use password
 * "password".
 *
 * NOT for production — it creates fictional clients, vendors and money.
 */
class SampleDataSeeder extends Seeder
{
    private const MARKER_EMAIL = 'info@banani-dental.sample.test';

    private CarbonImmutable $today;

    /** @var array<string, User> */
    private array $staff = [];

    /** @var Employee[] */
    private array $employees = [];

    /** @var Client[] */
    private array $clients = [];

    /** @var array<string, Project[]> keyed by status bucket */
    private array $projects = [];

    /** @var array<string, Warehouse> */
    private array $warehouses = [];

    /** @var Product[] */
    private array $products = [];

    /** @var Vendor[] */
    private array $vendors = [];

    /** @var array<string, ExpenseCategory> */
    private array $expenseCategories = [];

    public function run(): void
    {
        if (Client::where('email', self::MARKER_EMAIL)->exists()) {
            $this->command?->warn('SampleDataSeeder: sample dataset already present — skipping.');

            return;
        }

        // The single-record walkthrough (and the foundation seeders it
        // calls) first; it is idempotent on its own.
        $this->call(DemoSeeder::class);

        mt_srand(20261017);
        $this->today = CarbonImmutable::today();

        DB::transaction(function () {
            $this->loadDemoFoundation();
            $this->seedEmployees();
            $this->seedClientsAndCrm();
            $this->seedProjects();
            $this->seedDesigns();
            $this->seedSalesAndBilling();
            $this->seedExpenses();
            $this->seedInventory();
            $this->seedProcurement();
            $this->seedMaterialRequisitionsAndTransfers();
            $this->seedAttendanceLeavesClaimsPayroll();
            $this->seedSubcontractors();
            $this->seedMeetingsQcAndReports();
            $this->seedHandoversWarrantiesSelections();
        });

        $this->command?->info('SampleDataSeeder: sample dataset created (staff password: "password").');
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    private function pick(array $items): mixed
    {
        return $items[mt_rand(0, count($items) - 1)];
    }

    private function daysAgo(int $days): string
    {
        return $this->today->subDays($days)->toDateString();
    }

    private function phone(): string
    {
        return '+8801'.mt_rand(3, 9).str_pad((string) mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
    }

    /** Skips Fridays (the weekly holiday in Bangladesh). */
    private function workday(int $daysAgo): string
    {
        $d = $this->today->subDays($daysAgo);

        return ($d->isFriday() ? $d->subDay() : $d)->toDateString();
    }

    private function allProjects(): array
    {
        return array_merge(...array_values($this->projects));
    }

    // -----------------------------------------------------------------
    // Foundation from DemoSeeder
    // -----------------------------------------------------------------

    private function loadDemoFoundation(): void
    {
        foreach ([
            'pm' => 'rafiq.pm@demo.test',
            'designer' => 'nadia.designer@demo.test',
            'site_engineer' => 'tanvir.site@demo.test',
            'accountant' => 'farhana.accounts@demo.test',
            'procurement' => 'imran.procurement@demo.test',
            'store' => 'shahin.store@demo.test',
            'hr' => 'sumaiya.hr@demo.test',
        ] as $key => $email) {
            $this->staff[$key] = User::where('email', $email)->firstOrFail();
        }

        $this->employees = Employee::orderBy('id')->get()->all();

        $this->warehouses = [
            'main' => Warehouse::where('name', 'Main Warehouse')->firstOrFail(),
            'site' => Warehouse::where('name', 'Greenview Site Store')->firstOrFail(),
            'furniture' => Warehouse::firstOrCreate(
                ['name' => 'Furniture Workshop Store'],
                ['type' => 'furniture', 'location' => 'Savar, Dhaka']
            ),
        ];

        foreach ([
            'materials' => 'Site Materials',
            'vendor' => 'Vendor Payments',
            'labour' => 'Labour & Wages',
            'transport' => 'Transport & Conveyance',
            'office' => 'Office & Utilities',
            'marketing' => 'Marketing',
            'tools' => 'Tools & Equipment',
        ] as $key => $name) {
            $this->expenseCategories[$key] = ExpenseCategory::firstOrCreate(['name' => $name], ['is_active' => true]);
        }
    }

    // -----------------------------------------------------------------
    // Employees (extra staff with and without logins)
    // -----------------------------------------------------------------

    private function seedEmployees(): void
    {
        $employeesSvc = app(EmployeeService::class);
        $users = app(\App\Services\UserService::class);

        $dept = fn (string $code) => Department::where('code', $code)->firstOrFail();
        $sales = Department::firstOrCreate(['code' => 'SAL'], ['name' => 'Sales & Business Development']);

        $des = [
            'sales' => Designation::firstOrCreate(['department_id' => $sales->id, 'name' => 'Sales Executive']),
            'supervisor' => Designation::firstOrCreate(['department_id' => $dept('OPS')->id, 'name' => 'Site Supervisor']),
            'carpenter' => Designation::firstOrCreate(['department_id' => $dept('OPS')->id, 'name' => 'Lead Carpenter']),
            'visualizer' => Designation::firstOrCreate(['department_id' => $dept('DSN')->id, 'name' => '3D Visualizer']),
            'store_keeper' => Designation::firstOrCreate(['department_id' => $dept('PRC')->id, 'name' => 'Store Keeper']),
            'accounts_officer' => Designation::firstOrCreate(['department_id' => $dept('ACC')->id, 'name' => 'Accounts Officer']),
        ];

        // Two extra logins so the CRM has more than one owner.
        foreach ([
            ['Arif Hossain', 'arif.sales@sample.test', 'sales', $sales, 52000],
            ['Lamia Chowdhury', 'lamia.sales@sample.test', 'sales', $sales, 48000],
        ] as [$name, $email, $desKey, $department, $salary]) {
            $user = $users->create([
                'name' => $name, 'email' => $email, 'phone' => $this->phone(),
                'password' => 'password', 'is_active' => true,
            ], ['employee']);

            $this->staff[$email] = $user;
            $this->employees[] = $employeesSvc->create([
                'user_id' => $user->id, 'name' => $name, 'email' => $email, 'phone' => $user->phone,
                'department_id' => $department->id, 'designation_id' => $des[$desKey]->id,
                'joining_date' => $this->daysAgo(mt_rand(200, 900)), 'salary' => $salary,
                'employment_type' => 'full_time', 'status' => 'active',
            ]);
        }

        // Field and back-office staff without system logins.
        foreach ([
            ['Jahid Hasan', 'supervisor', 'OPS', 32000, 'full_time'],
            ['Monir Uddin', 'carpenter', 'OPS', 26000, 'full_time'],
            ['Sohel Rana', 'carpenter', 'OPS', 24000, 'full_time'],
            ['Rupa Begum', 'visualizer', 'DSN', 38000, 'full_time'],
            ['Kawsar Ahmed', 'store_keeper', 'PRC', 22000, 'full_time'],
            ['Nusrat Jahan', 'accounts_officer', 'ACC', 30000, 'full_time'],
            ['Habib Mollah', 'supervisor', 'OPS', 30000, 'contract'],
            ['Rakib Hasan', 'visualizer', 'DSN', 18000, 'part_time'],
        ] as [$name, $desKey, $code, $salary, $type]) {
            $this->employees[] = $employeesSvc->create([
                'name' => $name, 'phone' => $this->phone(),
                'department_id' => $dept($code)->id, 'designation_id' => $des[$desKey]->id,
                'joining_date' => $this->daysAgo(mt_rand(120, 1100)), 'salary' => $salary,
                'employment_type' => $type, 'status' => 'active',
                'address' => $this->pick(['Mirpur 10, Dhaka', 'Mohammadpur, Dhaka', 'Uttara Sector 7, Dhaka', 'Badda, Dhaka', 'Jatrabari, Dhaka']),
                'emergency_contact' => $this->phone(),
            ]);
        }

        // One former employee, for the inactive filter.
        $this->employees[] = $employeesSvc->create([
            'name' => 'Selim Reza', 'phone' => $this->phone(),
            'department_id' => $dept('OPS')->id, 'designation_id' => $des['supervisor']->id,
            'joining_date' => $this->daysAgo(1400), 'salary' => 28000,
            'employment_type' => 'full_time', 'status' => 'terminated',
        ]);
    }

    // -----------------------------------------------------------------
    // CRM: clients, contacts, leads, follow-ups, site visits
    // -----------------------------------------------------------------

    private function seedClientsAndCrm(): void
    {
        $clientsSvc = app(ClientService::class);
        $leadsSvc = app(LeadService::class);
        $visitSvc = app(SiteVisitService::class);
        $owners = [$this->staff['pm'], $this->staff['arif.sales@sample.test'], $this->staff['lamia.sales@sample.test']];

        $clientRows = [
            ['Banani Dental Clinic', 'company', self::MARKER_EMAIL, 'Road 27, Banani, Dhaka', 'Dr. Tahmina Akter'],
            ['Rahman Group Holdings', 'company', 'admin@rahman-group.sample.test', 'Motijheel C/A, Dhaka', 'Mahbub Rahman'],
            ['Gulshan Heights Apartments', 'company', 'office@gulshan-heights.sample.test', 'Gulshan Avenue, Dhaka', 'Sharif Uddin'],
            ['Uttara Corporate Center', 'company', 'facilities@ucc.sample.test', 'Sector 4, Uttara, Dhaka', 'Rezaul Karim'],
            ['Spice Route Restaurant', 'company', 'owner@spiceroute.sample.test', 'Dhanmondi 27, Dhaka', 'Anwar Hossain'],
            ['Lakeview Boutique Hotel', 'company', 'gm@lakeview.sample.test', 'Gulshan Lake Road, Dhaka', 'Sabrina Khan'],
            ['Aarong-style Showroom Ltd.', 'company', 'store@showroom.sample.test', 'Bashundhara City, Dhaka', 'Mizanur Rahman'],
            ['BrightPath School', 'company', 'principal@brightpath.sample.test', 'Mirpur DOHS, Dhaka', 'Nasreen Sultana'],
            ['Chattogram Port Logistics', 'company', 'ops@cpl.sample.test', 'Agrabad, Chattogram', 'Abdul Kader'],
            ['Tasnim Rahman', 'individual', 'tasnim.r@sample.test', 'Road 12, Dhanmondi, Dhaka', null],
            ['Shafiqul Islam', 'individual', 'shafiq.islam@sample.test', 'Bashundhara R/A Block C, Dhaka', null],
            ['Ayesha Siddiqua', 'individual', 'ayesha.s@sample.test', 'Uttara Sector 13, Dhaka', null],
            ['Kamrul Hasan', 'individual', 'kamrul.h@sample.test', 'Baridhara DOHS, Dhaka', null],
            ['Nabila Ferdous', 'individual', 'nabila.f@sample.test', 'Mohakhali DOHS, Dhaka', null],
            ['Zubair Alam', 'individual', 'zubair.alam@sample.test', 'Niketan, Gulshan 1, Dhaka', null],
            ['Rumana Haque', 'individual', 'rumana.h@sample.test', 'Lalmatia, Dhaka', null],
        ];

        foreach ($clientRows as [$name, $type, $email, $address, $contact]) {
            $client = $clientsSvc->create([
                'name' => $name, 'type' => $type, 'phone' => $this->phone(), 'email' => $email,
                'company_name' => $type === 'company' ? $name : null,
                'address' => $address, 'billing_address' => $address, 'site_address' => $address,
                'assigned_to' => $this->pick($owners)->id,
            ]);

            if ($contact) {
                $clientsSvc->addContact($client, [
                    'name' => $contact, 'designation' => $this->pick(['Managing Director', 'Admin Manager', 'Owner', 'Facilities Head']),
                    'phone' => $this->phone(), 'email' => null, 'is_primary' => true,
                ]);
            }

            $this->clients[] = $client;
        }

        // Leads in every pipeline stage, created over the last 6 months.
        $sources = LeadSource::all()->all();
        $firstNames = ['Sadia', 'Tanim', 'Fahim', 'Mithila', 'Rafsan', 'Tahsin', 'Nafisa', 'Asif', 'Priya', 'Shuvo', 'Ishrat', 'Rifat', 'Jannat', 'Omar', 'Lubna'];
        $lastNames = ['Ahmed', 'Karim', 'Hossain', 'Chowdhury', 'Rahman', 'Islam', 'Sarkar', 'Das', 'Mahmud', 'Haque'];
        $statusMix = array_merge(
            array_fill(0, 7, 'new'), array_fill(0, 6, 'contacted'), array_fill(0, 6, 'follow_up'),
            array_fill(0, 5, 'site_visit'), array_fill(0, 5, 'proposal'), array_fill(0, 4, 'negotiation'),
            array_fill(0, 7, 'won'), array_fill(0, 5, 'lost'),
        );
        $services = ['Full Interior', 'Kitchen Remodel', 'Office Fit-out', 'Restaurant Interior', 'Bedroom Makeover', 'False Ceiling & Lighting', 'Showroom Design'];

        foreach ($statusMix as $i => $status) {
            $created = $this->today->subDays(mt_rand(2, 180));
            $lead = $leadsSvc->create([
                'name' => $this->pick($firstNames).' '.$this->pick($lastNames),
                'phone' => $this->phone(),
                'email' => 'lead'.($i + 1).'@sample.test',
                'project_type' => $this->pick(['residential', 'residential', 'office', 'restaurant', 'commercial', 'showroom', 'renovation']),
                'estimated_budget' => mt_rand(4, 120) * 25000,
                'interested_service' => $this->pick($services),
                'lead_source_id' => $sources ? $this->pick($sources)->id : null,
                'assigned_to' => $this->pick($owners)->id,
                'priority' => $this->pick(['low', 'medium', 'medium', 'high']),
                'status' => $status,
                'expected_closing_date' => $created->addDays(mt_rand(15, 60))->toDateString(),
                'address' => $this->pick(['Dhanmondi', 'Gulshan', 'Banani', 'Uttara', 'Mirpur', 'Bashundhara']).', Dhaka',
                'notes' => $status === 'lost' ? $this->pick(['Budget mismatch.', 'Chose a competitor.', 'Project postponed indefinitely.']) : null,
            ]);
            $lead->forceFill(['created_at' => $created, 'updated_at' => $created])->saveQuietly();

            if ($status !== 'new') {
                foreach (range(1, mt_rand(1, 3)) as $n) {
                    $leadsSvc->addFollowup($lead, [
                        'date' => $created->addDays($n * 3)->toDateString(),
                        'type' => $this->pick(['call', 'whatsapp', 'email', 'meeting', 'site_visit']),
                        'notes' => $this->pick([
                            'Shared portfolio and rough budget range.',
                            'Client wants a 3D concept before committing.',
                            'Discussed timeline; prefers to start after Eid.',
                            'Sent revised estimate over WhatsApp.',
                            'Scheduled measurement visit.',
                        ]),
                        'next_followup_date' => $created->addDays($n * 3 + 4)->toDateString(),
                        'assigned_to' => $lead->assigned_to,
                    ]);
                }
            }

            if ($status === 'won' && $i % 2 === 0) {
                $this->clients[] = $leadsSvc->convertToClient($lead, ['assigned_to' => $lead->assigned_to]);
            }
        }

        // Measurement / survey visits, past and upcoming.
        foreach (range(1, 12) as $n) {
            $client = $this->pick($this->clients);
            $visitSvc->create([
                'client_id' => $client->id,
                'visit_date' => $n <= 8 ? $this->daysAgo(mt_rand(3, 150)) : $this->today->addDays(mt_rand(1, 14))->toDateString(),
                'assigned_to' => $this->pick([$this->staff['site_engineer'], $this->staff['designer']])->id,
                'purpose' => $this->pick(['Initial measurement', 'Site survey', 'Design discussion on site', 'Electrical layout check']),
                'site_address' => $client->site_address ?? $client->address,
                'requirements' => $this->pick(['Modern minimal look, lots of storage.', 'Warm wood tones, hidden lighting.', 'Client wants an open kitchen.', null]),
            ], [
                ['area_name' => 'Living Room', 'length' => mt_rand(14, 24), 'width' => mt_rand(12, 18), 'height' => 10, 'notes' => null],
                ['area_name' => $this->pick(['Master Bedroom', 'Kitchen', 'Reception', 'Dining']), 'length' => mt_rand(10, 16), 'width' => mt_rand(10, 14), 'height' => 10, 'notes' => null],
            ]);
        }
    }

    // -----------------------------------------------------------------
    // Projects with milestones, tasks and team
    // -----------------------------------------------------------------

    private function seedProjects(): void
    {
        $svc = app(ProjectService::class);

        $plan = [
            // [status, progress, started days ago, duration days, type]
            ['completed', 100, 330, 150, 'residential'],
            ['completed', 100, 270, 120, 'office'],
            ['completed', 100, 220, 110, 'restaurant'],
            ['handover', 96, 160, 140, 'residential'],
            ['inspection', 88, 140, 130, 'commercial'],
            ['execution', 65, 110, 150, 'residential'],
            ['execution', 48, 80, 140, 'hotel'],
            ['execution', 35, 60, 120, 'office'],
            ['procurement', 25, 45, 120, 'showroom'],
            ['approved', 15, 30, 110, 'residential'],
            ['quotation', 8, 20, 100, 'restaurant'],
            ['design', 5, 12, 120, 'residential'],
            ['on_hold', 40, 150, 160, 'commercial'],
            ['cancelled', 10, 200, 120, 'renovation'],
        ];

        foreach ($plan as $i => [$status, $progress, $startedAgo, $duration, $type]) {
            $client = $this->clients[$i % count($this->clients)];
            $start = $this->today->subDays($startedAgo);
            $budget = mt_rand(8, 90) * 50000;

            $project = $svc->create([
                'client_id' => $client->id,
                'name' => $client->name.' — '.match ($type) {
                    'office' => 'Office Fit-out', 'restaurant' => 'Restaurant Interior', 'hotel' => 'Guest Room Refurbishment',
                    'commercial' => 'Commercial Space Interior', 'showroom' => 'Showroom Design & Build',
                    'renovation' => 'Renovation Works', default => 'Residential Interior',
                },
                'type' => $type,
                'location' => $client->site_address ?? $client->address,
                'start_date' => $start->toDateString(),
                'expected_end_date' => $start->addDays($duration)->toDateString(),
                'actual_end_date' => $status === 'completed' ? $start->addDays($duration - mt_rand(0, 10))->toDateString() : null,
                'estimated_budget' => $budget,
                'project_manager_id' => $this->staff['pm']->id,
                'designer_id' => $this->staff['designer']->id,
                'site_engineer_id' => $this->staff['site_engineer']->id,
                'priority' => $this->pick(['low', 'medium', 'medium', 'high']),
                'status' => $status,
                'description' => "Sample {$type} project — {$status}.",
            ]);
            $project->update(['progress' => $progress]);

            $phases = [
                ['Concept & Design Sign-off', 0, 0.2],
                ['BOQ & Material Selection', 0.15, 0.35],
                ['Civil, Ceiling & Electrical', 0.3, 0.6],
                ['Carpentry & Furniture', 0.5, 0.85],
                ['Finishing & Handover', 0.8, 1.0],
            ];
            foreach ($phases as [$name, $from, $to]) {
                $phaseProgress = $progress >= $to * 100 ? 100 : ($progress <= $from * 100 ? 0 : (int) round(($progress - $from * 100) / (($to - $from) * 100) * 100));
                $due = $start->addDays((int) round($duration * $to));
                $svc->addMilestone($project, [
                    'name' => $name,
                    'start_date' => $start->addDays((int) round($duration * $from))->toDateString(),
                    'due_date' => $due->toDateString(),
                    'assigned_to' => $this->pick([$this->staff['designer'], $this->staff['site_engineer'], $this->staff['pm']])->id,
                    'status' => $phaseProgress >= 100 ? 'completed'
                        : ($status === 'cancelled' ? 'cancelled'
                        : ($phaseProgress > 0 ? ($due->isPast() ? 'delayed' : 'in_progress') : 'pending')),
                    'progress' => $phaseProgress,
                    'priority' => 'medium',
                ]);
            }

            foreach ([
                'Confirm final measurements', 'Order hardware & fittings', 'Install false ceiling frame',
                'Electrical first fix', 'Wardrobe fabrication', 'Paint second coat', 'Client walkthrough',
            ] as $t => $taskName) {
                $done = $progress >= ($t + 1) * 14;
                $svc->addTask($project, [
                    'name' => $taskName,
                    'assigned_to' => $this->pick([$this->staff['site_engineer'], $this->staff['designer']])->id,
                    'due_date' => $start->addDays(($t + 1) * (int) ($duration / 8))->toDateString(),
                    'status' => $done ? 'completed' : ($progress >= $t * 14 ? 'in_progress' : 'todo'),
                    'progress' => $done ? 100 : ($progress >= $t * 14 ? 50 : 0),
                    'priority' => $this->pick(['low', 'medium', 'high']),
                ]);
            }

            $svc->addMember($project, $this->staff['pm']->id, 'Project Manager');
            $svc->addMember($project, $this->staff['designer']->id, 'Designer');
            $svc->addMember($project, $this->staff['site_engineer']->id, 'Site Engineer');

            $bucket = match ($status) {
                'completed', 'handover' => 'done',
                'inspection', 'execution', 'procurement' => 'active',
                'approved', 'quotation', 'design' => 'early',
                default => 'other',
            };
            $this->projects[$bucket][] = $project;
        }
    }

    // -----------------------------------------------------------------
    // Designs, revisions, client approvals
    // -----------------------------------------------------------------

    private function seedDesigns(): void
    {
        $svc = app(DesignService::class);
        $categories = ['floor_plan', 'furniture', 'ceiling', 'electrical_layout', 'lighting', 'rendering', 'elevation'];

        foreach (array_merge($this->projects['active'], $this->projects['early'], $this->projects['done']) as $i => $project) {
            foreach (range(1, mt_rand(1, 3)) as $n) {
                $category = $this->pick($categories);
                $design = $svc->create([
                    'project_id' => $project->id,
                    'title' => ucwords(str_replace('_', ' ', $category)).' — '.$this->pick(['Living & Dining', 'Master Bedroom', 'Kitchen', 'Reception', 'Conference Room']),
                    'category' => $category,
                    'description' => 'Sample design set.',
                    'status' => 'draft',
                ]);

                $revisions = mt_rand(1, 2);
                foreach (range(1, $revisions) as $v) {
                    $revision = $design->revisions()->create([
                        'version' => $v,
                        'drawing_no' => sprintf('DRG-%03d-%02d', $design->id, $v),
                        'issue_date' => $this->daysAgo(mt_rand(5, 120)),
                        'uploaded_by' => $this->staff['designer']->id,
                        'description' => $v === 1 ? 'First issue.' : 'Revised per client comments.',
                        'status' => 'submitted',
                    ]);

                    // Early projects keep their latest revision under review.
                    if ($v < $revisions || ! in_array($project, $this->projects['early'], true)) {
                        $svc->recordClientApproval(
                            $revision,
                            $v < $revisions ? 'revision_requested' : $this->pick(['approved', 'approved', 'approved', 'rejected']),
                            $v < $revisions ? 'Please make the storage wall deeper.' : 'Looks good.',
                        );
                    } else {
                        $design->update(['status' => 'under_review']);
                    }
                }
            }
        }
    }

    // -----------------------------------------------------------------
    // BOQ → Quotation (with versions) → Contract → Schedule → Change
    // orders → Invoices → Payments
    // -----------------------------------------------------------------

    private function seedSalesAndBilling(): void
    {
        $boqSvc = app(BoqService::class);
        $quoteSvc = app(QuotationService::class);
        $contractSvc = app(ContractService::class);
        $scheduleSvc = app(PaymentScheduleService::class);
        $coSvc = app(ChangeOrderService::class);
        $invoiceSvc = app(InvoiceService::class);
        $paymentSvc = app(PaymentService::class);

        $catalog = [
            ['False Ceiling', 'Gypsum board false ceiling with cove lighting', 'Ceiling', 'sqft', 160, 220],
            ['Wardrobe', 'Built-in wardrobe, laminated board, soft-close', 'Carpentry', 'unit', 65000, 110000],
            ['Kitchen Cabinet', 'Modular kitchen cabinet, upper and lower', 'Carpentry', 'rft', 9000, 15000],
            ['Wall Paint', 'Premium emulsion, 2 coats', 'Finishing', 'sqft', 45, 75],
            ['Wall Panelling', 'Veneer wall panel with grooves', 'Carpentry', 'sqft', 650, 1100],
            ['Lighting Fixtures', 'LED downlights and strip lights', 'Electrical', 'set', 25000, 60000],
            ['Floor Tiles', 'Porcelain tiles, supply and fixing', 'Flooring', 'sqft', 180, 320],
            ['Workstation', 'Office workstation with storage', 'Furniture', 'unit', 18000, 32000],
            ['Curtains & Blinds', 'Motorised blinds and sheer curtains', 'Soft Furnishing', 'sqft', 220, 400],
        ];

        $billable = array_merge($this->projects['done'], $this->projects['active'], $this->projects['early'], $this->projects['other']);

        foreach ($billable as $p => $project) {
            $items = [];
            foreach (array_rand($catalog, mt_rand(3, 5)) as $idx) {
                [$item, $desc, $cat, $unit, $min, $max] = $catalog[$idx];
                $qty = $unit === 'sqft' ? mt_rand(300, 1800) : ($unit === 'rft' ? mt_rand(12, 30) : mt_rand(1, 6));
                $items[] = ['item' => $item, 'description' => $desc, 'category' => $cat, 'qty' => $qty, 'unit' => $unit, 'rate' => mt_rand($min, $max)];
            }
            $boq = $boqSvc->create($project, ['status' => 'active'], $items);

            $quotation = $quoteSvc->create($project, [
                'discount' => mt_rand(0, 5) * 10000,
                'tax' => 0,
                'terms' => '50% advance, 30% at mid-point, 20% on handover.',
            ], [], $boq);

            $status = $project->status->value;

            if ($status === 'quotation') {
                // Sent, then revised once (snapshots quotation_versions)
                // and sent again — now under negotiation.
                $quotation = $quoteSvc->changeStatus($quotation, 'sent');
                $quotation = $quoteSvc->update($quotation, ['discount' => (float) $quotation->discount + 25000, 'notes' => 'Revised: extra discount on carpentry.'],
                    $quotation->items->map(fn ($i) => [
                        'type' => $i->type->value, 'description' => $i->description,
                        'qty' => (float) $i->qty, 'unit' => $i->unit, 'rate' => (float) $i->rate,
                    ])->all(),
                    $this->staff['pm']);
                $quoteSvc->changeStatus($quotation, 'sent');
                $quoteSvc->changeStatus($quotation->fresh(), 'negotiation');

                continue;
            }

            if ($status === 'design') {
                continue; // still a draft quotation
            }

            if ($status === 'cancelled') {
                $quoteSvc->changeStatus($quotation, 'sent');
                $quoteSvc->changeStatus($quotation, 'rejected');

                continue;
            }

            $quoteSvc->changeStatus($quotation, 'sent');
            $quoteSvc->changeStatus($quotation, 'approved');
            $quotation = $quotation->fresh(['items']);

            $start = CarbonImmutable::instance($project->start_date);
            $end = CarbonImmutable::instance($project->expected_end_date);

            $contract = $contractSvc->createFromQuotation($quotation, [
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'payment_terms' => '50/30/20 milestone-based',
                'warranty_terms' => '12 months on workmanship.',
                'status' => $status === 'completed' ? 'completed' : 'active',
            ]);
            $project->update(['contract_value' => $contract->amount]);

            $stages = [
                ['Advance (50%)', 50, $start],
                ['Mid-term (30%)', 30, $start->addDays((int) ($start->diffInDays($end) / 2))],
                ['On Handover (20%)', 20, $end],
            ];
            foreach ($stages as [$label, $pct, $due]) {
                $scheduleSvc->addLine($contract, ['label' => $label, 'percentage' => $pct, 'due_date' => $due->toDateString()]);
            }

            if ($p % 3 === 0) {
                $co = $coSvc->create($project, ['description' => $this->pick([
                    'Additional TV wall unit in family lounge.',
                    'Upgrade to imported hardware for all cabinets.',
                    'Extra lighting in corridor and staircase.',
                ])], [
                    ['description' => 'Additional scope as requested', 'qty' => 1, 'rate' => mt_rand(4, 20) * 5000],
                ]);
                $decision = $this->pick(['approved', 'approved', 'rejected', null]);
                if ($decision) {
                    $coSvc->recordClientDecision($co, $decision);
                }
            }

            // Invoices follow the schedule up to how far the project has got.
            $amount = (float) $contract->amount;
            $progress = (int) $project->progress;
            foreach ($stages as $s => [$label, $pct, $due]) {
                if (($s === 1 && $progress < 40) || ($s === 2 && $progress < 95) || $due->isFuture()) {
                    break;
                }
                if ($status === 'on_hold' && $s > 0) {
                    break;
                }

                $invoiceDate = $due->subDays(mt_rand(0, 5));
                $invoice = $invoiceSvc->create($project, [
                    'contract_id' => $contract->id,
                    'type' => match ($s) { 0 => 'advance', 2 => 'final', default => 'progress' },
                    'invoice_date' => $invoiceDate->toDateString(),
                    'due_date' => $invoiceDate->addDays(15)->toDateString(),
                ], [
                    ['description' => "{$label} — {$project->name}", 'qty' => 1, 'rate' => round($amount * $pct / 100, 2)],
                ]);
                $invoice = $invoiceSvc->issue($invoice);

                $this->payInvoice($paymentSvc, $invoice, $project, $invoiceDate);
            }
        }

        // A couple of draft and one cancelled invoice for the status chart.
        $active = $this->projects['active'][0];
        $invoiceSvc->create($active, ['type' => 'material', 'invoice_date' => $this->daysAgo(1)], [
            ['description' => 'Imported hardware supplied (draft)', 'qty' => 1, 'rate' => 86000],
        ]);
        $cancel = $invoiceSvc->issue($invoiceSvc->create($active, ['type' => 'additional_work', 'invoice_date' => $this->daysAgo(40), 'due_date' => $this->daysAgo(25)], [
            ['description' => 'Duplicate billing — cancelled', 'qty' => 1, 'rate' => 45000],
        ]));
        $invoiceSvc->cancel($cancel);
    }

    /**
     * Pays an issued invoice the way real clients do: older invoices are
     * mostly settled (sometimes in two instalments), recent ones are often
     * still open, and a few slip past their due date unpaid (overdue).
     */
    private function payInvoice(PaymentService $svc, Invoice $invoice, Project $project, CarbonImmutable $invoiceDate): void
    {
        $age = $invoiceDate->diffInDays($this->today);
        $total = (float) $invoice->grand_total;
        $roll = mt_rand(1, 100);

        $plan = match (true) {
            $age > 60 => $roll <= 85 ? [1.0] : ($roll <= 95 ? [0.6] : []),
            $age > 25 => $roll <= 55 ? [1.0] : ($roll <= 80 ? [0.5] : []),
            default => $roll <= 30 ? [1.0] : ($roll <= 50 ? [0.4] : []),
        };

        // Split some full payments into two instalments.
        if ($plan === [1.0] && mt_rand(1, 3) === 1) {
            $plan = [0.6, 0.4];
        }

        $paidSoFar = 0.0;
        foreach ($plan as $k => $share) {
            $amount = $k === count($plan) - 1 && array_sum($plan) >= 1.0 ? round($total - $paidSoFar, 2) : round($total * $share, 2);
            $date = $invoiceDate->addDays(mt_rand(3, 20) + $k * 12);
            if ($date->isFuture()) {
                $date = $this->today;
            }

            $svc->create([
                'client_id' => $project->client_id,
                'project_id' => $project->id,
                'invoice_id' => $invoice->id,
                'date' => $date->toDateString(),
                'amount' => $amount,
                'method' => $this->pick(['bank', 'bank', 'cheque', 'bkash', 'online_transfer', 'cash']),
                'reference' => 'TXN-'.strtoupper(substr(md5($invoice->invoice_no.$k), 0, 8)),
                'account' => $this->pick(['City Bank — Operating A/C', 'BRAC Bank — Collections A/C', 'Cash in Hand']),
            ]);
            $paidSoFar += $amount;
        }
    }

    // -----------------------------------------------------------------
    // Expenses — daily spread over 6 months
    // -----------------------------------------------------------------

    private function seedExpenses(): void
    {
        $svc = app(ExpenseService::class);
        $projects = array_merge($this->projects['done'], $this->projects['active'], $this->projects['early']);

        $templates = [
            ['materials', 'Cement, sand and site consumables', 6000, 45000],
            ['materials', 'Plywood and laminate sheets', 25000, 120000],
            ['materials', 'Paint and putty', 8000, 40000],
            ['labour', 'Daily labour payment', 5000, 30000],
            ['transport', 'Truck rent for material delivery', 2500, 9000],
            ['transport', 'Staff transport to site', 500, 3000],
            ['tools', 'Power tool repair / blades', 1500, 15000],
            ['office', 'Office electricity and internet', 8000, 22000],
            ['marketing', 'Facebook ads boost', 5000, 25000],
        ];

        foreach (range(0, 179) as $daysAgo) {
            $d = $this->today->subDays($daysAgo);
            if ($d->isFriday() || mt_rand(1, 100) > 75) {
                continue;
            }

            foreach (range(1, mt_rand(1, 2)) as $n) {
                [$cat, $desc, $min, $max] = $this->pick($templates);
                $live = array_values(array_filter($projects, fn ($p) => CarbonImmutable::instance($p->start_date)->lte($d)));
                if (! $live) {
                    continue;
                }

                $svc->create([
                    'project_id' => $this->pick($live)->id,
                    'category_id' => $this->expenseCategories[$cat]->id,
                    'vendor_name' => $this->pick(['Nawabpur Hardware', 'Bismillah Traders', 'Local supplier', null]),
                    'amount' => round(mt_rand($min, $max) / 100) * 100,
                    'date' => $d->toDateString(),
                    'method' => $this->pick(['cash', 'cash', 'bank', 'bkash', 'nagad']),
                    'description' => $desc,
                    'created_by' => $this->staff['accountant']->id,
                ]);
            }
        }
    }

    // -----------------------------------------------------------------
    // Inventory: categories, units, products, opening stock
    // -----------------------------------------------------------------

    private function seedInventory(): void
    {
        $inv = app(InventoryService::class);

        $cats = [];
        foreach (['Board & Panels', 'Hardware & Fittings', 'Paint & Chemicals', 'Electrical', 'Tiles & Stone'] as $name) {
            $cats[$name] = ProductCategory::firstOrCreate(['name' => $name], ['is_active' => true]);
        }

        $units = [];
        foreach ([['Piece', 'PCS'], ['Sheet', 'SHT'], ['Litre', 'LTR'], ['Square Feet', 'SFT'], ['Box', 'BOX'], ['Roll', 'ROL']] as [$name, $code]) {
            $units[$code] = Unit::firstOrCreate(['short_code' => $code], ['name' => $name]);
        }

        $rows = [
            // sku, name, category, brand, unit, price, min, [main, site, furniture]
            ['BRD-MDF-18', '18mm MDF Board 8x4', 'Board & Panels', 'Akij', 'SHT', 2650, 30, [120, 10, 40]],
            ['BRD-PLB-18', '18mm Particle Board 8x4', 'Board & Panels', 'Partex', 'SHT', 1850, 30, [18, 0, 6]],
            ['BRD-LAM-01', 'High-Gloss Laminate Sheet', 'Board & Panels', 'Greenlam', 'SHT', 1450, 40, [95, 0, 30]],
            ['BRD-VNR-OAK', 'Oak Veneer Sheet', 'Board & Panels', 'Century', 'SHT', 3800, 15, [22, 0, 8]],
            ['HRD-HNG-SC', 'Soft-Close Hinge (pair)', 'Hardware & Fittings', 'Hettich', 'PCS', 450, 100, [340, 40, 60]],
            ['HRD-CHN-45', 'Telescopic Drawer Channel 45cm', 'Hardware & Fittings', 'Ebco', 'PCS', 680, 60, [44, 6, 10]],
            ['HRD-HDL-SS', 'SS Cabinet Handle 160mm', 'Hardware & Fittings', 'Hafele', 'PCS', 320, 80, [210, 0, 40]],
            ['HRD-LOK-01', 'Wardrobe Lock', 'Hardware & Fittings', 'Godrej', 'PCS', 550, 30, [12, 0, 4]],
            ['PNT-EML-PR', 'Premium Emulsion Paint', 'Paint & Chemicals', 'Berger', 'LTR', 620, 80, [260, 30, 0]],
            ['PNT-PTY-01', 'Wall Putty (20kg)', 'Paint & Chemicals', 'Asian Paints', 'BOX', 1250, 25, [40, 8, 0]],
            ['PNT-ADH-01', 'Wood Adhesive (5kg)', 'Paint & Chemicals', 'Fevicol', 'BOX', 1650, 20, [9, 2, 6]],
            ['PNT-PU-01', 'PU Lacquer (4L)', 'Paint & Chemicals', 'Asian Paints', 'BOX', 4200, 10, [16, 0, 5]],
            ['ELC-DL-12', 'LED Downlight 12W', 'Electrical', 'Philips', 'PCS', 480, 100, [420, 60, 0]],
            ['ELC-STP-5M', 'LED Strip Light 5m', 'Electrical', 'Osram', 'ROL', 1150, 40, [75, 10, 0]],
            ['ELC-CBL-15', '1.5mm Electrical Cable (90m)', 'Electrical', 'BRB', 'ROL', 3400, 20, [14, 3, 0]],
            ['ELC-SWT-01', 'Modular Switch Socket', 'Electrical', 'Legrand', 'PCS', 260, 150, [480, 50, 0]],
            ['TIL-POR-60', 'Porcelain Tile 60x60', 'Tiles & Stone', 'RAK', 'SFT', 145, 800, [2400, 300, 0]],
            ['TIL-WAL-30', 'Wall Tile 30x60', 'Tiles & Stone', 'Mir', 'SFT', 95, 600, [380, 0, 0]],
            ['TIL-GRN-01', 'Granite Slab (Black Galaxy)', 'Tiles & Stone', 'Imported', 'SFT', 520, 100, [260, 0, 0]],
            ['TIL-QTZ-01', 'Quartz Countertop Slab', 'Tiles & Stone', 'Imported', 'SFT', 980, 60, [70, 0, 20]],
        ];

        foreach ($rows as [$sku, $name, $cat, $brand, $unit, $price, $min, $stock]) {
            $product = Product::firstOrCreate(['sku' => $sku], [
                'name' => $name, 'category_id' => $cats[$cat]->id, 'brand' => $brand,
                'unit_id' => $units[$unit]->id, 'purchase_price' => $price, 'usage_rate' => 1,
                'min_stock' => $min, 'description' => $name.' — sample stock item.',
            ]);
            $this->products[] = $product;

            foreach (['main', 'site', 'furniture'] as $w => $key) {
                if ($stock[$w] > 0) {
                    $inv->openingStock($product, $this->warehouses[$key], $stock[$w], $this->staff['store']->id);
                }
            }
        }

        // A few stock corrections (damage / count adjustment).
        $inv->adjust($this->products[4], $this->warehouses['main'], -6, $this->staff['store']->id);
        $inv->adjust($this->products[12], $this->warehouses['main'], 12, $this->staff['store']->id);
    }

    // -----------------------------------------------------------------
    // Procurement: vendors, PR → VQs → PO → GRN → vendor invoices
    // -----------------------------------------------------------------

    private function seedProcurement(): void
    {
        $vendorSvc = app(VendorService::class);
        $prSvc = app(PurchaseRequestService::class);
        $vqSvc = app(VendorQuotationService::class);
        $poSvc = app(PurchaseOrderService::class);
        $grnSvc = app(GoodsReceiveService::class);
        $viSvc = app(VendorInvoiceService::class);

        $vendorRows = [
            ['Akij Board Mills Distributor', 'Habibur Rahman', 'Board & Panels', [0, 1, 2, 3]],
            ['Hafele Bangladesh Partner', 'Tareq Aziz', 'Hardware', [4, 5, 6, 7]],
            ['Berger Paint Point', 'Shamim Ahmed', 'Paint', [8, 9, 10, 11]],
            ['Bijli Electricals', 'Nazmul Huda', 'Electrical', [12, 13, 14, 15]],
            ['RAK Tiles Gallery', 'Faisal Kabir', 'Tiles', [16, 17, 18, 19]],
            ['Nawabpur Hardware Mart', 'Abul Kalam', 'Hardware', [4, 5, 6, 7, 10]],
        ];

        foreach ($vendorRows as $v => [$company, $person, $category, $productIdx]) {
            $vendor = $vendorSvc->create([
                'company_name' => $company, 'contact_person' => $person, 'phone' => $this->phone(),
                'email' => 'sales'.($v + 1).'@vendor.sample.test', 'address' => $this->pick(['Nawabpur, Dhaka', 'Tejgaon I/A, Dhaka', 'Kaptan Bazar, Dhaka', 'Agrabad, Chattogram']),
                'category' => $category, 'tax_info' => 'TIN-'.mt_rand(100000000, 999999999),
            ]);

            $attach = [];
            foreach ($productIdx as $idx) {
                $attach[$this->products[$idx]->id] = ['vendor_price' => round((float) $this->products[$idx]->purchase_price * (mt_rand(88, 98) / 100), 2)];
            }
            $vendor->products()->attach($attach);
            $this->vendors[] = $vendor;
        }

        $projects = $this->projects['active'];
        $cycles = [
            // [vendor A idx, vendor B idx, product idx list, receive: full|partial|none, vendor invoice: approved|submitted|rejected|null]
            [0, 5, [0, 2], 'full', 'approved'],
            [1, 5, [4, 5, 6], 'full', 'approved'],
            [2, 5, [8, 9], 'partial', 'submitted'],
            [3, 1, [12, 13, 15], 'none', null],
            [4, 0, [16, 18], 'full', 'rejected'],
        ];

        foreach ($cycles as $c => [$a, $b, $productIdx, $receive, $viState]) {
            $project = $projects[$c % count($projects)];
            $when = $this->today->subDays(70 - $c * 12);

            $items = array_map(fn ($i) => ['product_id' => $this->products[$i]->id, 'quantity' => mt_rand(10, 60)], $productIdx);
            $pr = $prSvc->create($project, ['requested_by' => $this->staff['procurement']->id], $items);
            $prSvc->sendForQuotation($pr);

            $quotes = [];
            foreach ([$a, $b] as $k => $vIdx) {
                $quotes[] = $vqSvc->create($pr, ['vendor_id' => $this->vendors[$vIdx]->id], array_map(fn ($it) => [
                    'product_id' => $it['product_id'],
                    'qty' => $it['quantity'],
                    'rate' => round((float) Product::find($it['product_id'])->purchase_price * ($k === 0 ? 0.93 : 0.98), 2),
                ], $items));
            }
            $winner = $quotes[0];
            $vqSvc->selectWinner($winner);

            $po = $poSvc->createFromVendorQuotation($winner->fresh(), [
                'warehouse_id' => $this->warehouses['main']->id,
                'date' => $when->toDateString(),
                'expected_delivery' => $when->addDays(10)->toDateString(),
            ]);
            $poSvc->send($po);
            $po = $po->fresh(['items']);

            if ($receive !== 'none') {
                $grnSvc->create($po, [
                    'received_date' => $when->addDays(8)->toDateString(),
                    'received_by' => $this->staff['store']->id,
                    'notes' => $receive === 'partial' ? 'Balance to follow next week.' : null,
                ], $po->items->map(fn ($item) => [
                    'purchase_order_item_id' => $item->id,
                    'received_qty' => $receive === 'partial' ? floor((float) $item->qty / 2) : (float) $item->qty,
                    'damaged_qty' => $receive === 'full' && mt_rand(1, 4) === 1 ? 1 : 0,
                ])->all());
            }

            if ($viState) {
                $vi = $viSvc->submit($po->fresh(), $this->vendors[$a], [
                    'vendor_invoice_no' => 'VINV-'.(1200 + $c),
                    'invoice_date' => $when->addDays(12)->toDateString(),
                    'amount' => (float) $po->fresh()->total,
                ]);

                if ($viState === 'approved') {
                    $viSvc->approve($vi, [
                        'category_id' => $this->expenseCategories['vendor']->id,
                        'date' => $when->addDays(18)->toDateString(),
                        'method' => 'bank',
                    ], $this->staff['procurement']->id);
                } elseif ($viState === 'rejected') {
                    $viSvc->reject($vi, 'Rates do not match the purchase order.', $this->staff['procurement']->id);
                }
            }
        }

        // One request still waiting for vendor quotes, one still draft.
        $prSvc->sendForQuotation($prSvc->create($projects[0], ['requested_by' => $this->staff['procurement']->id], [
            ['product_id' => $this->products[3]->id, 'quantity' => 12],
        ]));
        $prSvc->create($projects[1 % count($projects)], ['requested_by' => $this->staff['site_engineer']->id], [
            ['product_id' => $this->products[19]->id, 'quantity' => 40],
        ]);
    }

    // -----------------------------------------------------------------
    // Material requisitions and stock transfers
    // -----------------------------------------------------------------

    private function seedMaterialRequisitionsAndTransfers(): void
    {
        $mrSvc = app(MaterialRequisitionService::class);
        $transferSvc = app(StockTransferService::class);

        $flows = ['issued', 'issued', 'issued', 'store_approved', 'manager_approved', 'pending', 'pending', 'rejected'];
        foreach ($flows as $f => $target) {
            $project = $this->projects['active'][$f % count($this->projects['active'])];
            $mr = $mrSvc->create($project, [
                'requested_by' => $this->staff['site_engineer']->id,
                'warehouse_id' => $this->warehouses['main']->id,
                'date' => $this->daysAgo(60 - $f * 7),
                'purpose' => $this->pick(['Kitchen cabinet fabrication', 'Ceiling lighting installation', 'Wardrobe hardware', 'Painting works', 'Floor tiling']),
            ], [
                // Only well-stocked lines, so every "issued" path has stock.
                ['product_id' => $this->products[$this->pick([0, 2, 4, 6, 8])]->id, 'quantity' => mt_rand(2, 8)],
                ['product_id' => $this->products[$this->pick([12, 13, 15, 16])]->id, 'quantity' => mt_rand(2, 8)],
            ]);

            if ($target === 'rejected') {
                $mrSvc->reject($mr);

                continue;
            }
            if ($target === 'pending') {
                continue;
            }
            $mrSvc->approveByManager($mr);
            if ($target === 'manager_approved') {
                continue;
            }
            $mrSvc->approveByStore($mr);
            if ($target === 'issued') {
                $mrSvc->issue($mr, $this->warehouses['main']->id, $this->staff['store']->id);
            }
        }

        // Main → site / workshop transfers in each state.
        foreach ([['site', 'completed'], ['furniture', 'completed'], ['site', 'in_transit'], ['furniture', 'pending']] as $t => [$to, $state]) {
            $transfer = $transferSvc->create([
                'from_warehouse_id' => $this->warehouses['main']->id,
                'to_warehouse_id' => $this->warehouses[$to]->id,
                'notes' => 'Stock for upcoming site work.',
            ], [
                ['product_id' => $this->products[$t]->id, 'quantity' => 4],
                ['product_id' => $this->products[$t + 4]->id, 'quantity' => 10],
            ]);

            if ($state !== 'pending') {
                $transferSvc->ship($transfer);
            }
            if ($state === 'completed') {
                $transferSvc->complete($transfer->fresh(), $this->staff['store']->id);
            }
        }
    }

    // -----------------------------------------------------------------
    // HR: attendance, leaves, expense claims, payroll
    // -----------------------------------------------------------------

    private function seedAttendanceLeavesClaimsPayroll(): void
    {
        $attendance = app(AttendanceService::class);
        $leaves = app(LeaveService::class);
        $claims = app(ExpenseClaimService::class);
        $payroll = app(PayrollService::class);

        $active = array_values(array_filter($this->employees, fn ($e) => $e->status->value === 'active'));

        // ~10 weeks of attendance (covers the current and previous month,
        // so payroll overtime/absence deductions compute from real rows).
        foreach ($active as $employee) {
            foreach (range(1, 70) as $daysAgo) {
                $d = $this->today->subDays($daysAgo);
                if ($d->isFriday()) {
                    continue;
                }

                $roll = mt_rand(1, 100);
                [$status, $in, $out] = match (true) {
                    $roll <= 72 => ['present', '09:'.str_pad((string) mt_rand(0, 10), 2, '0', STR_PAD_LEFT), '18:'.str_pad((string) mt_rand(0, 15), 2, '0', STR_PAD_LEFT)],
                    $roll <= 84 => ['late', '09:'.mt_rand(31, 59), '18:'.str_pad((string) mt_rand(10, 40), 2, '0', STR_PAD_LEFT)],
                    $roll <= 92 => ['overtime', '09:00', (string) mt_rand(19, 21).':'.str_pad((string) mt_rand(0, 45), 2, '0', STR_PAD_LEFT)],
                    $roll <= 96 => ['absent', null, null],
                    default => ['leave', null, null],
                };

                $date = $d->toDateString();
                $attendance->create([
                    'employee_id' => $employee->id,
                    'date' => $date,
                    'check_in' => $in ? "{$date} {$in}:00" : null,
                    'check_out' => $out ? "{$date} {$out}:00" : null,
                    'status' => $status,
                ]);
            }
        }

        // Leaves in every approval state.
        $leavePlan = [
            ['casual', -20, 1, 'approved'], ['sick', -35, 2, 'approved'], ['annual', -55, 4, 'approved'],
            ['emergency', -12, 0, 'approved'], ['casual', 6, 1, 'pending'], ['annual', 14, 5, 'pending'],
            ['sick', -8, 1, 'manager_only'], ['casual', -40, 0, 'rejected'], ['other', 20, 2, 'pending'],
            ['annual', -75, 6, 'approved'],
        ];
        foreach ($leavePlan as $l => [$type, $offset, $length, $state]) {
            $from = $this->today->addDays($offset);
            $leave = $leaves->create($active[$l % count($active)], [
                'type' => $type,
                'from_date' => $from->toDateString(),
                'to_date' => $from->addDays($length)->toDateString(),
                'reason' => $this->pick(['Family event out of town.', 'Fever and doctor visit.', 'Annual vacation.', 'Child’s school programme.', 'Personal work.']),
            ]);

            match ($state) {
                'approved' => [$leaves->approveByManager($leave), $leaves->approveByHr($leave)],
                'manager_only' => $leaves->approveByManager($leave),
                'rejected' => $leaves->reject($leave, 'manager'),
                default => null,
            };
        }

        // Conveyance / site-visit expense claims over the last 2 months.
        $fieldStaff = array_slice($active, 0, 8);
        $claimProjects = $this->projects['active'];
        $approved = [];
        foreach (range(1, 24) as $n) {
            $employee = $this->pick($fieldStaff);
            $type = $this->pick(['conveyance', 'conveyance', 'conveyance', 'meal', 'other']);
            $claim = $claims->create($employee, [
                'project_id' => $this->pick($claimProjects)->id,
                'type' => $type,
                'date' => $this->workday(mt_rand(1, 58)),
                'amount' => match ($type) { 'conveyance' => mt_rand(3, 18) * 50, 'meal' => mt_rand(3, 8) * 50, default => mt_rand(4, 20) * 100 },
                'description' => match ($type) {
                    'conveyance' => $this->pick(['CNG to site and back.', 'Uber to client meeting.', 'Rickshaw + bus to warehouse.']),
                    'meal' => 'Lunch during full-day site supervision.',
                    default => 'Printing drawings for client meeting.',
                },
            ]);

            $roll = mt_rand(1, 100);
            if ($roll <= 65) {
                $approved[] = $claims->approve($claim, $this->staff['hr']);
            } elseif ($roll <= 80) {
                $claims->reject($claim, $this->staff['hr']);
            }
        }

        // Payroll: two months ago paid, last month finalized, this month
        // draft for half the team. Figures come from computeDraft() so
        // they match the attendance and approved claims seeded above.
        $months = [
            $this->today->subMonthsNoOverflow(2)->format('Y-m') => 'paid',
            $this->today->subMonthNoOverflow()->format('Y-m') => 'finalized',
            $this->today->format('Y-m') => 'draft',
        ];
        foreach ($months as $month => $state) {
            foreach ($active as $e => $employee) {
                if ($state === 'draft' && $e % 2 === 1) {
                    continue;
                }

                $draft = $payroll->computeDraft($employee, $month);
                $row = $payroll->generate($employee, $month, [
                    'basic' => $draft['basic'],
                    'allowance' => round((float) $employee->salary * 0.1, 2),
                    'conveyance' => $draft['conveyance'],
                    'overtime' => $draft['overtime'],
                    'bonus' => $state === 'paid' && $e % 4 === 0 ? 5000 : 0,
                    'deduction' => $draft['deduction'],
                    'advance' => $e % 5 === 0 ? 2000 : 0,
                    'claim_ids' => $draft['claim_ids'],
                ]);

                if ($state !== 'draft') {
                    $row = $payroll->finalize($row);
                }
                if ($state === 'paid') {
                    $payroll->markPaid($row);
                }
            }
        }
    }

    // -----------------------------------------------------------------
    // Subcontractors, assignments, payments
    // -----------------------------------------------------------------

    private function seedSubcontractors(): void
    {
        $subSvc = app(SubcontractorService::class);
        $assignSvc = app(SubcontractorAssignmentService::class);
        $paySvc = app(SubcontractorPaymentService::class);

        $subs = [];
        foreach ([
            ['Karim Woodworks', 'carpenter', 'per sqft'], ['Shapla Paint Team', 'painter', 'per sqft'],
            ['Rahim Plumbing Solutions', 'plumber', 'lump sum'], ['Gypsum Craft BD', 'gypsum', 'per sqft'],
            ['ClearView Glass & Aluminium', 'glass', 'lump sum'], ['Sylhet Tile Masters', 'tile', 'per sqft'],
        ] as [$name, $trade, $rateType]) {
            $subs[] = $subSvc->create(['name' => $name, 'trade' => $trade, 'phone' => $this->phone(), 'rate_type' => $rateType]);
        }

        foreach (array_merge($this->projects['done'], $this->projects['active']) as $p => $project) {
            foreach (range(1, mt_rand(1, 2)) as $n) {
                $sub = $subs[($p + $n) % count($subs)];
                $rate = mt_rand(6, 40) * 10000;
                $progress = min(100, (int) $project->progress + mt_rand(-10, 10));
                $assignment = $assignSvc->create($project, [
                    'subcontractor_id' => $sub->id,
                    'work_description' => ucfirst($sub->trade->value).' works as per BOQ.',
                    'rate' => $rate,
                    'progress' => max(0, $progress),
                ]);

                $paidShare = $progress >= 100 ? 1.0 : round($progress / 100 * 0.8, 2);
                if ($paidShare > 0) {
                    $paySvc->create($assignment, [
                        'date' => $this->daysAgo(mt_rand(5, 90)),
                        'amount' => round($rate * $paidShare, 2),
                        'notes' => $progress >= 100 ? 'Final settlement.' : 'Running bill.',
                    ]);
                }
            }
        }
    }

    // -----------------------------------------------------------------
    // Meetings, inspections, snags, site reports
    // -----------------------------------------------------------------

    private function seedMeetingsQcAndReports(): void
    {
        $meetingSvc = app(MeetingService::class);
        $inspectionSvc = app(InspectionService::class);
        $snagSvc = app(SnagService::class);
        $reportSvc = app(SiteReportService::class);

        foreach (range(1, 10) as $n) {
            $project = $this->pick($this->allProjects());
            $upcoming = $n > 7;
            $meetingSvc->create([
                'project_id' => $project->id,
                'type' => $this->pick(['client', 'site', 'internal', 'vendor']),
                'date' => $upcoming ? $this->today->addDays(mt_rand(1, 10))->toDateString() : $this->daysAgo(mt_rand(2, 90)),
                'time' => $this->pick(['10:00', '11:30', '15:00', '17:00']),
                'location' => $this->pick(['Project site', 'PS Solution office', 'Client office', 'Google Meet']),
                'agenda' => $this->pick(['Design presentation', 'Material selection', 'Progress review', 'Payment follow-up', 'Snag walkthrough']),
                'discussion' => $upcoming ? null : 'Reviewed progress against schedule and open items.',
                'decisions' => $upcoming ? null : $this->pick(['Proceed with option B.', 'Client to confirm colours by Sunday.', 'Next site visit fixed for next week.']),
            ], [
                ['type' => 'user', 'id' => $this->staff['pm']->id],
                ['type' => 'user', 'id' => $this->pick([$this->staff['designer'], $this->staff['site_engineer']])->id],
            ]);
        }

        $snagPaths = ['closed', 'verified', 'fixed', 'in_progress', 'assigned', 'pending'];
        $s = 0;
        foreach (array_merge($this->projects['done'], array_slice($this->projects['active'], 0, 2)) as $project) {
            $inspection = $inspectionSvc->create($project, [
                'area' => $this->pick(['Living Room', 'Kitchen', 'Master Bedroom', 'Reception', 'Work Area']),
                'inspection_date' => $this->daysAgo(mt_rand(5, 60)),
                'inspector_id' => $this->staff['site_engineer']->id,
            ], [
                ['checklist_item' => 'Paint finish — no patches or brush marks', 'result' => 'passed'],
                ['checklist_item' => 'Cabinet doors aligned, soft-close working', 'result' => $this->pick(['passed', 'requires_correction'])],
                ['checklist_item' => 'All light points working', 'result' => $this->pick(['passed', 'passed', 'failed'])],
                ['checklist_item' => 'Ceiling joints invisible', 'result' => $this->pick(['passed', 'requires_correction']), 'notes' => 'Check under strong light.'],
            ]);

            foreach (range(1, 2) as $n) {
                $path = $snagPaths[$s++ % count($snagPaths)];
                $snag = $snagSvc->create($project, [
                    'area' => $inspection->area,
                    'issue' => $this->pick(['Hairline crack at ceiling joint.', 'Drawer channel not closing smoothly.', 'Paint touch-up needed near switchboard.', 'Tile edge chipped near door.', 'Strip light flickering.']),
                ], $inspection);

                $steps = ['assign', 'startProgress', 'markFixed', 'verify', 'close'];
                $stopAt = array_search($path, ['assigned', 'in_progress', 'fixed', 'verified', 'closed'], true);
                if ($stopAt === false) {
                    continue; // pending
                }
                foreach (array_slice($steps, 0, $stopAt + 1) as $step) {
                    $step === 'assign' ? $snagSvc->assign($snag, $this->staff['site_engineer']->id) : $snagSvc->{$step}($snag);
                }
            }
        }

        foreach ($this->projects['active'] as $project) {
            foreach (range(1, 4) as $n) {
                $reportSvc->create($project, [
                    'reported_by' => $this->staff['site_engineer']->id,
                    'report_date' => $this->workday($n * 4 + mt_rand(0, 2)),
                    'weather' => $this->pick(['Sunny', 'Cloudy', 'Light rain', 'Hot and humid']),
                    'manpower_count' => mt_rand(4, 14),
                    'work_summary' => $this->pick([
                        'Ceiling framing completed in living area; board fixing started.',
                        'Kitchen carcass installed; shutters to arrive tomorrow.',
                        'Electrical conduits laid in bedrooms; inspection pending.',
                        'Second coat of paint in corridor and dining.',
                        'Tiling 60% done in reception.',
                    ]),
                    'issues' => $this->pick([null, null, 'Material delivery delayed by one day.', 'Power cut for 2 hours in the afternoon.']),
                ]);
            }
        }
    }

    // -----------------------------------------------------------------
    // Handovers, warranties, service requests, selection boards
    // -----------------------------------------------------------------

    private function seedHandoversWarrantiesSelections(): void
    {
        $handoverSvc = app(HandoverService::class);
        $warrantySvc = app(WarrantyService::class);
        $srSvc = app(ServiceRequestService::class);
        $boardSvc = app(SelectionBoardService::class);

        foreach ($this->projects['done'] as $d => $project) {
            $date = CarbonImmutable::instance($project->actual_end_date ?? $project->expected_end_date);
            if ($date->isFuture()) {
                $date = $this->today->subDays(2);
            }

            $handover = $handoverSvc->create($project, [
                'handover_date' => $date->toDateString(),
                'notes' => 'Keys, manuals and as-built drawings handed over.',
            ]);
            if ($project->status->value === 'completed') {
                $handoverSvc->confirmByClient($handover);
            }

            $warranty = $warrantySvc->create($project, [
                'start_date' => $date->toDateString(),
                'end_date' => $date->addMonths(12)->toDateString(),
                'terms' => '12 months on workmanship; fittings per manufacturer warranty.',
            ]);

            if ($d < 3) {
                $sr = $srSvc->create($warranty, [
                    'client_id' => $project->client_id,
                    'complaint' => $this->pick(['Kitchen drawer runner is sticking.', 'Wardrobe door hinge loose.', 'Paint peeling in bathroom ceiling.']),
                ]);
                match ($d) {
                    0 => [$srSvc->assignTechnician($sr, $this->staff['site_engineer']->id), $srSvc->scheduleSiteVisit($sr), $srSvc->markRepair($sr), $srSvc->markVerification($sr), $srSvc->close($sr)],
                    1 => [$srSvc->assignTechnician($sr, $this->staff['site_engineer']->id), $srSvc->scheduleSiteVisit($sr)],
                    default => null,
                };
            }
        }

        foreach (array_merge($this->projects['active'], $this->projects['early']) as $b => $project) {
            $board = $boardSvc->create([
                'project_id' => $project->id,
                'area' => $this->pick(['Kitchen', 'Master Bedroom', 'Living Room', 'Reception']),
                'title' => $this->pick(['Countertop Material', 'Wardrobe Laminate Colour', 'Feature Wall Finish', 'Lighting Fixture Style']),
                'description' => 'Please choose one option.',
            ], $this->staff['designer']);

            $first = $boardSvc->addOption($board, ['label' => 'Option A — Premium', 'price' => mt_rand(30, 60) * 100, 'notes' => 'Recommended.']);
            $boardSvc->addOption($board, ['label' => 'Option B — Standard', 'price' => mt_rand(15, 29) * 100]);
            $boardSvc->addOption($board, ['label' => 'Option C — Budget', 'price' => mt_rand(8, 14) * 100]);

            if ($b % 3 !== 2) {
                $board = $boardSvc->send($board);
                if ($b % 3 === 0) {
                    $boardSvc->recordDecision($board, $first, 'Going with the premium option.');
                }
            }
        }
    }
}
