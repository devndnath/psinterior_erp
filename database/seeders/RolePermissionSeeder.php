<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Generates the full permission set programmatically (spec §3: granular,
 * never hard-coded in a controller) and assigns the 14 default roles their
 * starting permission sets. Re-runnable: every call is firstOrCreate /
 * syncPermissions, so running the seeder again after a new module adds
 * permissions just adds the new ones without duplicating or wiping
 * existing role assignments an Admin may have customized.
 *
 * Client, Vendor and Subcontractor are NOT given Spatie roles here: they
 * authenticate on the `client`/`vendor` guards (ARCHITECTURE.md §5) and
 * their access is decided by ownership checks in Policies ("a client sees
 * only their own records" — spec §78 rules 10-11), not by a permission
 * list. Spatie roles exist only for the 11 staff roles on the `web` guard.
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * module => extra non-CRUD abilities beyond the standard
     * view/create/edit/delete set generated for every module below.
     */
    protected array $extraAbilities = [
        'invoices' => ['approve_invoice'],
        'designs' => ['upload_design', 'approve_design'],
        'quotations' => ['approve_quotation'],
        'purchase_orders' => ['approve_purchase'],
        'change_orders' => ['approve_change_order'],
        'inventory' => ['manage_inventory'],
        'employees' => ['manage_employees'],
        'payroll' => ['manage_payroll'],
    ];

    /** Modules that get the standard view/create/edit/delete set. */
    protected array $crudModules = [
        'leads', 'clients', 'site_visits', 'projects', 'milestones', 'tasks',
        'designs', 'documents', 'boqs', 'quotations', 'contracts', 'change_orders',
        'invoices', 'payments', 'expenses', 'inventory', 'warehouses',
        'material_requisitions', 'vendors', 'purchase_orders', 'goods_receives',
        'employees', 'attendance', 'leaves', 'payroll', 'subcontractors',
        'meetings', 'inspections', 'snags', 'handovers', 'warranties',
        'users', 'roles',
    ];

    /** Stand-alone abilities with no view/create/edit/delete siblings. */
    protected array $standaloneAbilities = [
        'view_finance', 'view_reports', 'manage_settings', 'view_all_companies',
    ];

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionNames = $this->buildPermissionList();

        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $this->seedRoles();

        Cache::forget(config('permission.cache.key'));
    }

    protected function buildPermissionList(): array
    {
        $names = [];

        foreach ($this->crudModules as $module) {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                $names[] = "{$action}_{$module}";
            }

            foreach ($this->extraAbilities[$module] ?? [] as $extra) {
                $names[] = $extra;
            }
        }

        return array_merge($names, $this->standaloneAbilities);
    }

    protected function seedRoles(): void
    {
        $all = Permission::where('guard_name', 'web')->pluck('name')->all();

        $definitions = [
            'super-admin' => $all, // also bypassed entirely by Gate::before — see AuthServiceProvider
            'admin' => array_values(array_diff($all, ['view_all_companies'])),

            'project-manager' => $this->forModules(['projects', 'milestones', 'tasks', 'boqs', 'change_orders'], ['view', 'create', 'edit'])
                + $this->forModules(['designs', 'quotations', 'contracts', 'documents', 'meetings', 'inspections', 'snags', 'handovers', 'material_requisitions'], ['view', 'create'])
                + $this->named(['view_finance', 'view_reports', 'approve_change_order']),

            'designer' => $this->forModules(['designs'], ['view', 'create', 'edit'])
                + $this->forModules(['projects', 'documents'], ['view'])
                + $this->named(['upload_design']),

            'site-engineer' => $this->forModules(['tasks', 'inspections', 'snags', 'material_requisitions'], ['view', 'create', 'edit'])
                + $this->forModules(['projects', 'milestones', 'documents'], ['view']),

            'site-supervisor' => $this->forModules(['tasks', 'attendance'], ['view', 'create', 'edit'])
                + $this->forModules(['projects', 'milestones', 'snags', 'inspections'], ['view']),

            'accountant' => $this->forModules(['invoices', 'payments', 'expenses'], ['view', 'create', 'edit'])
                + $this->forModules(['quotations', 'contracts', 'change_orders'], ['view'])
                + $this->named(['approve_invoice', 'view_finance', 'view_reports']),

            'procurement-officer' => $this->forModules(['purchase_orders', 'goods_receives', 'material_requisitions', 'vendors'], ['view', 'create', 'edit'])
                + $this->forModules(['inventory', 'warehouses'], ['view']),

            'store-manager' => $this->forModules(['inventory', 'warehouses', 'goods_receives', 'material_requisitions'], ['view', 'create', 'edit'])
                + $this->named(['manage_inventory']),

            'hr-manager' => $this->forModules(['employees', 'attendance', 'leaves', 'payroll'], ['view', 'create', 'edit'])
                + $this->named(['manage_employees', 'manage_payroll', 'view_reports']),

            'employee' => $this->forModules(['tasks', 'attendance'], ['view'])
                + $this->forModules(['projects'], ['view']),
        ];

        foreach ($definitions as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions(array_values(array_unique($permissions)));
        }
    }

    /** @return array<string,string> permission name => name (for array_merge-friendly union via +) */
    protected function forModules(array $modules, array $actions): array
    {
        $out = [];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $name = "{$action}_{$module}";
                $out[$name] = $name;
            }
        }

        return $out;
    }

    protected function named(array $names): array
    {
        return array_combine($names, $names);
    }
}
