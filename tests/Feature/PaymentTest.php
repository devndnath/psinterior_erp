<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Livewire\Payments\Index as PaymentIndex;
use App\Livewire\Payments\Show as PaymentShow;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use App\Services\PaymentService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo(['view_payments', 'create_payments', 'delete_payments']);
    }

    public function test_a_user_without_view_permission_cannot_see_the_payment_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(PaymentIndex::class)
            ->assertForbidden();
    }

    public function test_recording_a_payment_against_an_invoice_updates_paid_amount_and_flips_status_at_thresholds(): void
    {
        $project = Project::factory()->create();
        $invoice = Invoice::factory()->for($project)->create([
            'grand_total' => 10000,
            'paid_amount' => 0,
            'status' => InvoiceStatus::Issued->value,
        ]);

        $service = app(PaymentService::class);

        $service->create([
            'client_id' => $invoice->client_id,
            'project_id' => $project->id,
            'invoice_id' => $invoice->id,
            'date' => now()->toDateString(),
            'amount' => 4000,
            'method' => 'bank',
        ]);

        $fresh = $invoice->fresh();
        $this->assertEquals(4000, $fresh->paid_amount);
        $this->assertSame(InvoiceStatus::PartiallyPaid, $fresh->status);

        $service->create([
            'client_id' => $invoice->client_id,
            'project_id' => $project->id,
            'invoice_id' => $invoice->id,
            'date' => now()->toDateString(),
            'amount' => 6000,
            'method' => 'bank',
        ]);

        $fresh = $invoice->fresh();
        $this->assertEquals(10000, $fresh->paid_amount);
        $this->assertSame(InvoiceStatus::Paid, $fresh->status);
    }

    public function test_deleting_a_payment_recalculates_the_invoice_back_down(): void
    {
        $project = Project::factory()->create();
        $invoice = Invoice::factory()->for($project)->create([
            'grand_total' => 10000,
            'paid_amount' => 0,
            'status' => InvoiceStatus::Issued->value,
        ]);

        $service = app(PaymentService::class);

        $payment = $service->create([
            'client_id' => $invoice->client_id,
            'project_id' => $project->id,
            'invoice_id' => $invoice->id,
            'date' => now()->toDateString(),
            'amount' => 10000,
            'method' => 'bank',
        ]);

        $this->assertSame(InvoiceStatus::Paid, $invoice->fresh()->status);

        Livewire::actingAs($this->staff)->test(PaymentShow::class, ['payment' => $payment])
            ->call('deletePayment')
            ->assertHasNoErrors();

        $fresh = $invoice->fresh();
        $this->assertEquals(0, $fresh->paid_amount);
        $this->assertSame(InvoiceStatus::Issued, $fresh->status);
        $this->assertNull(Payment::find($payment->id));
    }

    public function test_deleting_a_payment_requires_delete_permission(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('view_payments');

        $project = Project::factory()->create();
        $payment = Payment::factory()->for($project)->create();

        Livewire::actingAs($viewer)->test(PaymentShow::class, ['payment' => $payment])
            ->call('deletePayment')
            ->assertForbidden();

        $this->assertNotNull($payment->fresh());
    }
}
