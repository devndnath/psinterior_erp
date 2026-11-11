<?php

namespace App\Reports;

use App\Reports\Definitions as D;

/**
 * Every report the engine knows, in menu order (the order of the firm's
 * previous system). The sidebar, the Reports hub and the viewer route all
 * read from here, so adding a report is: write its class, list it here.
 */
class Registry
{
    /** @return array<int, class-string<Report>> */
    public static function classes(): array
    {
        return [
            // Accounts Report
            D\IncomeStatement::class,
            D\CashBook::class,
            // CRM Report
            D\ClientReport::class,
            D\EmployeeReport::class,
            // All Report
            D\OfficeReport::class,
            D\HeadWiseOfficeExpense::class,
            D\EmployeeConveyance::class,
            D\ArchitectWise::class,
            D\ProjectWise::class,
            D\ContractorWise::class,
            D\AccountantWise::class,
            D\EmployeeWiseAll::class,
            D\ProjectProductPurchase::class,
            D\ClientPayment::class,
            D\VendorPayment::class,
            D\VendorProductPurchase::class,
            D\CategoryWise::class,
            D\EstimatedBudget::class,
            D\ProfitReport::class,
            D\VatReport::class,
            D\EmployeeDailyTask::class,
            D\EmployeeSalary::class,
            D\MethodWise::class,
        ];
    }

    /** @return class-string<Report>|null */
    public static function find(string $key): ?string
    {
        foreach (self::classes() as $class) {
            if ($class::key() === $key) {
                return $class;
            }
        }

        return null;
    }

    /** Reports in a group the user may open, as menu links. */
    public static function menu(string $group): array
    {
        return self::visibleMenu()[$group] ?? [];
    }

    /**
     * Every report the signed-in user may open, as menu links grouped by
     * menu group — worked out once per request (the sidebar asks for each
     * group on every page; checking all reports' permissions per group
     * was a large share of the page's render time).
     */
    private static function visibleMenu(): array
    {
        $user = auth()->user();
        if (! $user || ! $user->can('view_reports')) {
            return [];
        }

        $cache = app()->bound('reports.menu') ? app('reports.menu') : null;
        if ($cache !== null && $cache['user'] === $user->getAuthIdentifier()) {
            return $cache['menu'];
        }

        $allowed = [];
        $menu = [];
        foreach (self::classes() as $c) {
            $permission = $c::permission();
            if ($allowed[$permission] ??= $user->can($permission)) {
                $menu[$c::group()][] = ['label' => $c::title(), 'route' => 'reports.view', 'params' => ['report' => $c::key()], 'key' => $c::key()];
            }
        }

        app()->instance('reports.menu', ['user' => $user->getAuthIdentifier(), 'menu' => $menu]);

        return $menu;
    }
}
