<?php

namespace Tests\Feature;

use App\Enums\InvoiceType;
use App\Livewire\Client\Designs\Index as ClientDesignIndex;
use App\Livewire\Client\Designs\Show as ClientDesignShow;
use App\Livewire\Designs\Form as DesignForm;
use App\Livewire\Designs\Index as DesignIndex;
use App\Livewire\Designs\Show as DesignShow;
use App\Livewire\Invoices\Form as InvoiceForm;
use App\Models\Client;
use App\Models\Design;
use App\Models\Invoice;
use App\Models\User;
use App\Services\DesignService;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * A design sold on its own: no project, its own client and price, listed
 * with project designs, invoiced through a Design Sale invoice linked
 * back to it, and visible to that client in the portal.
 */
class SoldDesignTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        Storage::fake('uploads');

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo([
            'view_designs', 'create_designs', 'edit_designs',
            'view_invoices', 'create_invoices', 'edit_invoices', 'approve_invoice', 'view_payments',
        ]);
    }

    private function soldDesign(Client $client, float $price = 30000): Design
    {
        return app(DesignService::class)->create([
            'project_id' => null, 'client_id' => $client->id,
            'title' => 'Penthouse 3D concept', 'category' => '3d_design', 'price' => $price, 'status' => 'approved',
        ]);
    }

    public function test_a_sold_design_is_created_without_a_project(): void
    {
        $client = Client::factory()->create();

        Livewire::actingAs($this->staff)->test(DesignForm::class)
            ->set('for', 'sold')
            ->set('client_id', (string) $client->id)
            ->set('title', 'Duplex concept')
            ->set('category', '3d_design')
            ->set('price', '45000')
            ->set('file', UploadedFile::fake()->create('concept.pdf', 200, 'application/pdf'))
            ->call('save')
            ->assertHasNoErrors();

        $design = Design::firstOrFail();
        $this->assertNull($design->project_id);
        $this->assertSame($client->id, $design->client_id);
        $this->assertEquals(45000, $design->price);
        $this->assertTrue($design->isSold());
    }

    public function test_a_sold_design_needs_a_client_and_a_price(): void
    {
        Livewire::actingAs($this->staff)->test(DesignForm::class)
            ->set('for', 'sold')
            ->set('title', 'X')
            ->set('file', UploadedFile::fake()->create('x.pdf', 10, 'application/pdf'))
            ->call('save')
            ->assertHasErrors(['client_id' => 'required_if', 'price' => 'required_if']);
    }

    public function test_the_list_filters_sold_designs_and_shows_price_and_payment(): void
    {
        $client = Client::factory()->create();
        $sold = $this->soldDesign($client, 30000);
        Design::factory()->create(['title' => 'Project ceiling plan']);

        $invoices = app(InvoiceService::class);
        $invoice = $invoices->issue($invoices->create(null, ['client_id' => $client->id, 'design_id' => $sold->id], [
            ['description' => 'Design', 'qty' => 1, 'rate' => 30000],
        ]));
        app(PaymentService::class)->create([
            'client_id' => $client->id, 'project_id' => null, 'invoice_id' => $invoice->id,
            'date' => now()->toDateString(), 'amount' => 30000, 'method' => 'bank',
        ]);

        Livewire::withQueryParams(['type' => 'sold'])->actingAs($this->staff)->test(DesignIndex::class)
            ->assertSee('Penthouse 3D concept')
            ->assertDontSee('Project ceiling plan')
            ->assertSee('Sold design')
            ->assertSee($client->name)
            ->assertSee('30,000.00')
            ->assertSee('Paid in full');
    }

    public function test_create_invoice_from_a_sold_design_prefills_and_links_it(): void
    {
        $client = Client::factory()->create();
        $design = $this->soldDesign($client, 27500);

        Livewire::withQueryParams(['billing' => 'design', 'design' => (string) $design->id])
            ->actingAs($this->staff)->test(InvoiceForm::class)
            ->assertSet('client_id', (string) $client->id)
            ->assertSet('design_id', (string) $design->id)
            ->assertSet('items.0.rate', '27500.00')
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::firstOrFail();
        $this->assertSame($design->id, $invoice->design_id);
        $this->assertSame(InvoiceType::Design, $invoice->type);
        $this->assertEquals(27500, $invoice->grand_total);
    }

    public function test_the_client_sees_their_sold_design_in_the_portal_and_others_cannot(): void
    {
        $client = Client::factory()->create();
        $other = Client::factory()->create();
        $design = $this->soldDesign($client);

        $owner = User::factory()->client()->create();
        $client->update(['user_id' => $owner->id]);
        $stranger = User::factory()->client()->create();
        $other->update(['user_id' => $stranger->id]);

        Livewire::actingAs($owner, 'client')->test(ClientDesignIndex::class)->assertSee('Penthouse 3D concept');
        Livewire::actingAs($owner, 'client')->test(ClientDesignShow::class, ['design' => $design])->assertOk();
        Livewire::actingAs($stranger, 'client')->test(ClientDesignShow::class, ['design' => $design])->assertForbidden();
    }

    public function test_the_sold_design_page_shows_the_sale_panel(): void
    {
        $client = Client::factory()->create();
        $design = $this->soldDesign($client, 12000);

        Livewire::actingAs($this->staff)->test(DesignShow::class, ['design' => $design])
            ->assertOk()
            ->assertSee('Sold design')
            ->assertSee($client->name)
            ->assertSee('12,000.00')
            ->assertSee('Not invoiced yet')
            ->assertSee('Create invoice');
    }
}
