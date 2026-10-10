<?php

namespace Tests\Feature;

use App\Enums\PayrollStatus;
use App\Livewire\Payroll\Show as PayrollShow;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\User;
use App\Services\PayrollService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PayrollTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_generate_computes_net_salary_from_the_six_components(): void
    {
        $employee = Employee::factory()->create();

        $payroll = app(PayrollService::class)->generate($employee, '2026-10', [
            'basic' => 50000,
            'allowance' => 5000,
            'overtime' => 2000,
            'bonus' => 1000,
            'deduction' => 500,
            'advance' => 1500,
        ]);

        // 50000 + 5000 + 2000 + 1000 - 500 - 1500 = 56000
        $this->assertEquals(56000, (float) $payroll->net_salary);
        $this->assertSame(PayrollStatus::Draft, $payroll->status);
    }

    public function test_generate_throws_when_a_finalized_payroll_already_exists_for_the_month(): void
    {
        $employee = Employee::factory()->create();
        $service = app(PayrollService::class);

        $payroll = $service->generate($employee, '2026-10', ['basic' => 50000]);
        $service->finalize($payroll);

        $this->expectException(\RuntimeException::class);

        $service->generate($employee, '2026-10', ['basic' => 60000]);
    }

    public function test_finalize_and_mark_paid_transition_correctly(): void
    {
        $employee = Employee::factory()->create();
        $service = app(PayrollService::class);

        $payroll = $service->generate($employee, '2026-10', ['basic' => 50000]);

        $finalized = $service->finalize($payroll);
        $this->assertSame(PayrollStatus::Finalized, $finalized->status);

        $paid = $service->markPaid($finalized);
        $this->assertSame(PayrollStatus::Paid, $paid->status);
    }

    public function test_finalize_refuses_a_non_draft_payroll(): void
    {
        $employee = Employee::factory()->create();
        $service = app(PayrollService::class);

        $payroll = $service->generate($employee, '2026-10', ['basic' => 50000]);
        $service->finalize($payroll);

        $this->expectException(\RuntimeException::class);

        $service->finalize($payroll->fresh());
    }

    /**
     * finalize()/markPaid() are gated on the distinct `manage_payroll`
     * ability (PayrollPolicy), not on `edit_payroll` — a user with only
     * `edit_payroll` must be forbidden from finalizing.
     */
    public function test_a_user_with_only_edit_payroll_cannot_finalize(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['view_payroll', 'edit_payroll']);

        $payroll = Payroll::factory()->create(['status' => PayrollStatus::Draft->value]);

        Livewire::actingAs($user)->test(PayrollShow::class, ['payroll' => $payroll])
            ->call('finalize')
            ->assertForbidden();
    }

    public function test_a_user_with_manage_payroll_can_finalize_and_mark_paid(): void
    {
        $hrManager = User::factory()->create();
        $hrManager->givePermissionTo(['view_payroll', 'manage_payroll']);

        $payroll = Payroll::factory()->create(['status' => PayrollStatus::Draft->value]);

        Livewire::actingAs($hrManager)->test(PayrollShow::class, ['payroll' => $payroll])
            ->call('finalize')
            ->assertHasNoErrors();

        $this->assertSame(PayrollStatus::Finalized, $payroll->fresh()->status);

        Livewire::actingAs($hrManager)->test(PayrollShow::class, ['payroll' => $payroll->fresh()])
            ->call('markPaid')
            ->assertHasNoErrors();

        $this->assertSame(PayrollStatus::Paid, $payroll->fresh()->status);
    }
}
