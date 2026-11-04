<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Livewire\Clients\Form as ClientForm;
use App\Livewire\Leads\Form as LeadForm;
use App\Livewire\Projects\Show as ProjectShow;
use App\Livewire\References\Index as ReferenceIndex;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Lead;
use App\Models\Project;
use App\Models\User;
use App\Services\LeadService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Reference — who introduced a lead/client (an existing client, a staff
 * member or someone else) — the References page, and the project page's
 * client card and expense columns.
 */
class ReferenceTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo([
            'view_leads', 'create_leads', 'edit_leads', 'view_clients', 'create_clients', 'edit_clients',
            'view_projects', 'view_expenses', 'view_finance',
        ]);
    }

    public function test_a_lead_records_who_referred_it(): void
    {
        Livewire::actingAs($this->staff)->test(LeadForm::class)
            ->set('name', 'Mr. Referred')
            ->set('reference_type', 'other')
            ->set('reference_name', 'Ar. Kamal Uddin')
            ->set('reference_phone', '+8801711000111')
            ->call('save')
            ->assertHasNoErrors();

        $lead = Lead::firstOrFail();
        $this->assertSame('other', $lead->reference_type);
        $this->assertSame('Ar. Kamal Uddin', $lead->referrerName());
        $this->assertSame('Other person', $lead->referrerKind());
    }

    public function test_the_chosen_referrer_is_required_and_other_kinds_are_cleared(): void
    {
        Livewire::actingAs($this->staff)->test(LeadForm::class)
            ->set('name', 'X')
            ->set('reference_type', 'client')
            ->call('save')
            ->assertHasErrors(['reference_client_id' => 'required_if']);

        $employee = Employee::factory()->create();
        Livewire::actingAs($this->staff)->test(LeadForm::class)
            ->set('name', 'Y')
            ->set('reference_name', 'leftover text')
            ->set('reference_type', 'employee')
            ->set('reference_employee_id', (string) $employee->id)
            ->call('save')
            ->assertHasNoErrors();

        $lead = Lead::where('name', 'Y')->firstOrFail();
        $this->assertSame($employee->id, $lead->reference_employee_id);
        $this->assertNull($lead->reference_name);
    }

    public function test_a_client_cannot_be_their_own_referrer(): void
    {
        $client = Client::factory()->create();

        Livewire::actingAs($this->staff)->test(ClientForm::class, ['client' => $client])
            ->set('reference_type', 'client')
            ->set('reference_client_id', (string) $client->id)
            ->call('save')
            ->assertHasErrors(['reference_client_id']);
    }

    public function test_converting_a_lead_carries_the_reference_to_the_client(): void
    {
        $referrer = Client::factory()->create();
        $lead = Lead::factory()->create([
            'status' => LeadStatus::Won->value,
            'reference_type' => 'client', 'reference_client_id' => $referrer->id,
        ]);

        $client = app(LeadService::class)->convertToClient($lead);

        $this->assertSame('client', $client->reference_type);
        $this->assertSame($referrer->id, $client->reference_client_id);
        $this->assertTrue($referrer->referredClients()->whereKey($client->id)->exists());
    }

    public function test_the_references_page_groups_clients_and_open_leads_by_referrer(): void
    {
        $referrer = Client::factory()->create(['name' => 'Banani Dental']);
        $a = Client::factory()->create(['reference_type' => 'client', 'reference_client_id' => $referrer->id]);
        Client::factory()->create(['reference_type' => 'client', 'reference_client_id' => $referrer->id]);
        Lead::factory()->create(['reference_type' => 'client', 'reference_client_id' => $referrer->id]);
        // "Other" referrers group by name + phone, ignoring case/formatting.
        Client::factory()->create(['reference_type' => 'other', 'reference_name' => 'Kamal', 'reference_phone' => '+880 1711-000111']);
        Client::factory()->create(['reference_type' => 'other', 'reference_name' => 'kamal ', 'reference_phone' => '+8801711000111']);

        $view = Livewire::actingAs($this->staff)->test(ReferenceIndex::class)->assertOk();
        $rows = collect($view->viewData('referrers'))->keyBy('name');

        $this->assertCount(2, $rows);
        $this->assertSame(2, $rows['Banani Dental']['clients']->count());
        $this->assertSame(1, $rows['Banani Dental']['leads']->count());
        $view->assertDontSee('Business brought')->assertDontSee('Received');
        $this->assertSame(2, $rows['Kamal']['clients']->count());

        $this->actingAs(User::factory()->create())->get(route('references.index'))->assertForbidden();
    }

    public function test_project_page_shows_the_client_card_and_expense_category_and_purpose(): void
    {
        $referrer = Client::factory()->create(['name' => 'Gulshan Heights']);
        $client = Client::factory()->create(['name' => 'Uttara Corporate', 'reference_type' => 'client', 'reference_client_id' => $referrer->id]);
        $project = Project::factory()->create(['client_id' => $client->id]);
        $category = ExpenseCategory::factory()->create(['name' => 'Site Materials']);
        Expense::factory()->create(['project_id' => $project->id, 'category_id' => $category->id, 'description' => 'Cement and sand', 'amount' => 21900]);
        Expense::factory()->create(['project_id' => $project->id, 'description' => 'Truck rent', 'amount' => 6200]);

        Livewire::actingAs($this->staff)->test(ProjectShow::class, ['project' => $project])
            ->assertOk()
            ->assertSee('Uttara Corporate')
            ->assertSee('Referred by')
            ->assertSee('Gulshan Heights')
            ->assertSee('Site Materials')
            ->assertSee('Cement and sand')
            ->assertSee('28,100.00'); // expense total
    }
}
