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
        $user = auth()->user();

        return collect(self::classes())
            ->filter(fn ($c) => $c::group() === $group && $user?->can('view_reports') && $user->can($c::permission()))
            ->map(fn ($c) => ['label' => $c::title(), 'route' => 'reports.view', 'params' => ['report' => $c::key()], 'key' => $c::key()])
            ->values()->all();
    }
}
