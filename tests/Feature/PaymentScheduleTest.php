<?php

namespace Tests\Feature;

use App\Enums\ContractStatus;
use App\Enums\InvoiceStatus;
use App\Livewire\Contracts\Show as ContractShow;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\PaymentScheduleLine;
use App\Models\Project;
use App\Models\User;
use App\Services\PaymentScheduleService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Covers the Phase 17f "payment schedule / advance tracking" fix-first
 * item: a contract's planned installments ("30% advance on signing, 30%
 * on design approval, ...") and whether what's actually been invoiced
 * and collected is keeping pace with that plan. Gated through
 * ContractPolicy's existing abilities, not a new permission module —
 * same as ContractItem — so permission tests reuse the existing
 * view_contracts/edit_contracts strings rather than inventing new ones.
 */
class PaymentScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo(['view_contracts', 'edit_contracts']);
    }

    public function test_adding_a_line_computes_its_amount_from_a_percentage_of_the_contract(): void
    {
        $contract = Contract::factory()->create(['amount' => 100000]);

        $line = app(PaymentScheduleService::class)->addLine($contract, [
            'label' => 'Advance',
            'percentage' => 30,
        ]);

        $this->assertEquals(30000, $line->amount);
    }

    public function test_adding_a_line_accepts_a_fixed_amount_without_a_percentage(): void
    {
        $contract = Contract::factory()->create(['amount' => 100000]);

        $line = app(PaymentScheduleService::class)->addLine($contract, [
            'label' => 'Advance',
            'amount' => 25000,
        ]);

        $this->assertEquals(25000, $line->amount);
        $this->assertNull($line->percentage);
    }

    public function test_adding_a_line_without_an_amount_or_percentage_is_rejected(): void
    {
        $contract = Contract::factory()->create(['amount' => 100000]);

        $this->expectException(ValidationException::class);

        app(PaymentScheduleService::class)->addLine($contract, ['label' => 'Advance']);
    }

    public function test_scheduled_lines_cannot_exceed_the_contract_amount(): void
    {
        $contract = Contract::factory()->create(['amount' => 100000]);
        $service = app(PaymentScheduleService::class);

        $service->addLine($contract, ['label' => 'Advance', 'amount' => 70000]);

        $this->expectException(ValidationException::class);

        $service->addLine($contract, ['label' => 'Balance', 'amount' => 40000]);
    }

    public function test_updating_a_line_excludes_its_own_previous_amount_from_the_overflow_check(): void
    {
        $contract = Contract::factory()->create(['amount' => 100000]);
        $service = app(PaymentScheduleService::class);

        $service->addLine($contract, ['label' => 'Advance', 'amount' => 70000]);
        $line = $service->addLine($contract, ['label' => 'Balance', 'amount' => 30000]);

        // Bumping this line from 30,000 to 30,000 again (no real change)
        // must not be rejected just because 70,000 + 30,000 == the total
        // — the check has to exclude the line's own prior amount first.
        $updated = $service->updateLine($line, ['label' => 'Balance', 'amount' => 30000]);

        $this->assertEquals(30000, $updated->amount);
    }

    public function test_a_line_already_linked_to_an_invoice_cannot_be_removed(): void
    {
        $contract = Contract::factory()->create(['amount' => 100000]);
        $service = app(PaymentScheduleService::class);

        $line = $service->addLine($contract, ['label' => 'Advance', 'amount' => 30000]);
        $invoice = Invoice::factory()->create(['contract_id' => $contract->id, 'project_id' => $contract->project_id]);
        $service->linkInvoice($line, $invoice);

        $this->expectException(ValidationException::class);

        $service->removeLine($line->fresh());
    }

    public function test_linking_an_invoice_from_a_different_contract_is_rejected(): void
    {
        $contract = Contract::factory()->create(['amount' => 100000]);
        $otherContract = Contract::factory()->create();
        $service = app(PaymentScheduleService::class);

        $line = $service->addLine($contract, ['label' => 'Advance', 'amount' => 30000]);
        $foreignInvoice = Invoice::factory()->create(['contract_id' => $otherContract->id]);

        $this->expectException(ValidationException::class);

        $service->linkInvoice($line, $foreignInvoice);
    }

    public function test_summary_reports_what_was_due_by_today_versus_what_has_actually_been_invoiced(): void
    {
        $project = Project::factory()->create();
        $contract = Contract::factory()->for($project)->create(['amount' => 100000]);
        $service = app(PaymentScheduleService::class);

        // Due in the past, already invoiced and fully paid.
        $invoicedLine = $service->addLine($contract, [
            'label' => 'Advance', 'amount' => 30000, 'due_date' => now()->subDays(10)->toDateString(),
        ]);
        $paidInvoice = Invoice::factory()->create([
            'contract_id' => $contract->id, 'project_id' => $project->id,
            'grand_total' => 30000, 'paid_amount' => 30000, 'status' => InvoiceStatus::Paid->value,
        ]);
        $service->linkInvoice($invoicedLine, $paidInvoice);

        // Due in the past, NOT yet invoiced — this is the "behind schedule" line.
        $service->addLine($contract, [
            'label' => 'Design Approval', 'amount' => 30000, 'due_date' => now()->subDays(2)->toDateString(),
        ]);

        // Due in the future — shouldn't count toward "due by today" at all.
        $service->addLine($contract, [
            'label' => 'Handover', 'amount' => 40000, 'due_date' => now()->addMonth()->toDateString(),
        ]);

        $summary = $service->summary($contract->fresh());

        $this->assertEquals(100000, $summary['total_scheduled']);
        $this->assertEquals(0, $summary['remaining_to_schedule']);
        $this->assertEquals(60000, $summary['due_by_today']); // advance + design approval
        $this->assertEquals(30000, $summary['invoiced_so_far']); // only the advance was invoiced
        $this->assertEquals(30000, $summary['collected_so_far']);
        $this->assertEquals(0, $summary['outstanding_invoiced']); // the one invoice raised was paid in full
        // Behind by the uninvoiced "Design Approval" installment.
        $this->assertEquals(-30000, $summary['variance_due_vs_invoiced']);
    }

    public function test_staff_can_add_edit_and_remove_an_installment_from_the_contract_page(): void
    {
        $contract = Contract::factory()->create(['amount' => 100000, 'status' => ContractStatus::Active->value]);

        Livewire::actingAs($this->staff)->test(ContractShow::class, ['contract' => $contract])
            ->call('toggleScheduleForm')
            ->set('scheduleLabel', 'Advance')
            ->set('schedulePercentage', '30')
            ->call('saveScheduleLine')
            ->assertHasNoErrors();

        $line = PaymentScheduleLine::first();
        $this->assertSame('Advance', $line->label);
        $this->assertEquals(30000, $line->amount);

        Livewire::actingAs($this->staff)->test(ContractShow::class, ['contract' => $contract])
            ->call('editScheduleLine', $line->id)
            ->set('scheduleLabel', 'Advance Payment')
            ->call('saveScheduleLine')
            ->assertHasNoErrors();

        $this->assertSame('Advance Payment', $line->fresh()->label);

        Livewire::actingAs($this->staff)->test(ContractShow::class, ['contract' => $contract])
            ->call('removeScheduleLine', $line->id)
            ->assertHasNoErrors();

        $this->assertNull(PaymentScheduleLine::find($line->id));
    }

    public function test_a_user_without_edit_permission_cannot_add_an_installment(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('view_contracts');

        $contract = Contract::factory()->create();

        Livewire::actingAs($viewer)->test(ContractShow::class, ['contract' => $contract])
            ->call('toggleScheduleForm')
            ->assertForbidden();
    }

    public function test_an_overflowing_installment_amount_shows_a_validation_error_instead_of_a_server_error(): void
    {
        $contract = Contract::factory()->create(['amount' => 50000]);
        PaymentScheduleLine::factory()->for($contract)->create(['amount' => 40000]);

        Livewire::actingAs($this->staff)->test(ContractShow::class, ['contract' => $contract])
            ->call('toggleScheduleForm')
            ->set('scheduleLabel', 'Balance')
            ->set('scheduleAmount', '20000')
            ->call('saveScheduleLine')
            ->assertHasErrors('scheduleAmount');

        $this->assertSame(1, $contract->scheduleLines()->count());
    }

    public function test_staff_can_link_and_unlink_an_invoice_to_an_installment(): void
    {
        $project = Project::factory()->create();
        $contract = Contract::factory()->for($project)->create(['amount' => 100000]);
        $line = PaymentScheduleLine::factory()->for($contract)->create(['amount' => 30000]);
        $invoice = Invoice::factory()->create(['contract_id' => $contract->id, 'project_id' => $project->id]);

        Livewire::actingAs($this->staff)->test(ContractShow::class, ['contract' => $contract])
            ->call('openLinkInvoiceForm', $line->id)
            ->set('linkInvoiceId', (string) $invoice->id)
            ->call('linkInvoice')
            ->assertHasNoErrors();

        $this->assertSame($invoice->id, $line->fresh()->invoice_id);

        Livewire::actingAs($this->staff)->test(ContractShow::class, ['contract' => $contract])
            ->call('unlinkInvoice', $line->id)
            ->assertHasNoErrors();

        $this->assertNull($line->fresh()->invoice_id);
    }
}
