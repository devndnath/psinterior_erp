<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Livewire\Invoices\Form as InvoiceForm;
use App\Livewire\Invoices\Index as InvoiceIndex;
use App\Livewire\Invoices\Show as InvoiceShow;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo([
            'view_invoices', 'create_invoices', 'edit_invoices', 'delete_invoices',
            'approve_invoice',
        ]);
    }

    public function test_a_user_without_view_permission_cannot_see_the_invoice_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(InvoiceIndex::class)
            ->assertForbidden();
    }

    public function test_creating_an_invoice_assigns_a_sequential_number_and_computes_grand_total_from_items(): void
    {
        $project = Project::factory()->create();

        Livewire::actingAs($this->staff)->test(InvoiceForm::class)
            ->set('project_id', (string) $project->id)
            ->set('invoice_date', now()->toDateString())
            ->set('discount', '500')
            ->set('tax', '100')
            ->set('items.0.description', 'Site work')
            ->set('items.0.qty', '10')
            ->set('items.0.rate', '1000')
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::first();
        $this->assertNotNull($invoice);
        $this->assertStringStartsWith('INV-', $invoice->invoice_no);
        $this->assertSame(InvoiceStatus::Draft, $invoice->status);
        $this->assertEquals(10000, $invoice->subtotal);
        $this->assertEquals(9600, $invoice->grand_total); // 10000 - 500 + 100
    }

    public function test_a_paid_invoice_cannot_be_deleted(): void
    {
        $project = Project::factory()->create();
        $invoice = Invoice::factory()->for($project)->create([
            'status' => InvoiceStatus::Paid->value,
            'grand_total' => 10000,
            'paid_amount' => 10000,
        ]);

        Livewire::actingAs($this->staff)->test(InvoiceShow::class, ['invoice' => $invoice])
            ->call('deleteInvoice')
            ->assertForbidden();

        $this->assertNotNull($invoice->fresh());
    }

    public function test_issuing_an_invoice_transitions_draft_to_issued_and_requires_approve_invoice_ability(): void
    {
        $editorOnly = User::factory()->create();
        $editorOnly->givePermissionTo(['view_invoices', 'edit_invoices']);

        $project = Project::factory()->create();
        $invoice = Invoice::factory()->for($project)->create(['status' => InvoiceStatus::Draft->value]);

        Livewire::actingAs($editorOnly)->test(InvoiceShow::class, ['invoice' => $invoice])
            ->call('issueInvoice')
            ->assertForbidden();

        Livewire::actingAs($this->staff)->test(InvoiceShow::class, ['invoice' => $invoice])
            ->call('issueInvoice')
            ->assertHasNoErrors();

        $this->assertSame(InvoiceStatus::Issued, $invoice->fresh()->status);
    }
}
