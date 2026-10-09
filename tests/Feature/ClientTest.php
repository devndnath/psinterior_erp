<?php

namespace Tests\Feature;

use App\Livewire\Clients\Form as ClientForm;
use App\Livewire\Clients\Index as ClientIndex;
use App\Livewire\Clients\Show as ClientShow;
use App\Models\Client;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo(['view_clients', 'create_clients', 'edit_clients', 'delete_clients']);
    }

    public function test_a_user_without_view_permission_cannot_see_the_client_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(ClientIndex::class)
            ->assertForbidden();
    }

    public function test_creating_a_client_assigns_a_sequential_client_number(): void
    {
        Livewire::actingAs($this->staff)->test(ClientForm::class)
            ->set('name', 'Acme Restaurant Group')
            ->set('type', 'company')
            ->call('save')
            ->assertHasNoErrors();

        $client = Client::first();
        $this->assertNotNull($client);
        $this->assertStringStartsWith('CLIENT-', $client->client_no);
    }

    public function test_name_is_required_to_create_a_client(): void
    {
        Livewire::actingAs($this->staff)->test(ClientForm::class)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_client_360_view_assembles_leads_and_site_visits(): void
    {
        $client = Client::factory()->create();
        \App\Models\Lead::factory()->create(['converted_client_id' => $client->id]);
        \App\Models\SiteVisit::factory()->create(['client_id' => $client->id]);

        Livewire::actingAs($this->staff)->test(ClientShow::class, ['client' => $client])
            ->assertOk()
            ->assertViewHas('leads')
            ->assertViewHas('siteVisits');
    }

    public function test_adding_a_primary_contact_unsets_the_previous_primary_contact(): void
    {
        $client = Client::factory()->create();
        $client->contacts()->create(['name' => 'Old Primary', 'is_primary' => true]);

        Livewire::actingAs($this->staff)->test(ClientShow::class, ['client' => $client])
            ->set('contact_name', 'New Primary')
            ->set('contact_is_primary', true)
            ->call('addContact')
            ->assertHasNoErrors();

        $this->assertTrue($client->contacts()->where('name', 'New Primary')->first()->is_primary);
        $this->assertFalse($client->contacts()->where('name', 'Old Primary')->first()->is_primary);
    }

    public function test_deleting_a_client_requires_delete_permission(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('view_clients');

        $client = Client::factory()->create();

        Livewire::actingAs($viewer)->test(ClientIndex::class)
            ->call('deleteClient', $client->id)
            ->assertForbidden();

        $this->assertNotNull($client->fresh());
    }

    public function test_client_list_can_be_searched_by_name(): void
    {
        Client::factory()->create(['name' => 'Searchable Client']);
        Client::factory()->create(['name' => 'Other Client']);

        Livewire::actingAs($this->staff)->test(ClientIndex::class)
            ->set('search', 'Searchable')
            ->assertSee('Searchable Client')
            ->assertDontSee('Other Client');
    }
}
