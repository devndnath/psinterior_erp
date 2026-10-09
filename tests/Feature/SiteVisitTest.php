<?php

namespace Tests\Feature;

use App\Livewire\SiteVisits\Form as SiteVisitForm;
use App\Livewire\SiteVisits\Index as SiteVisitIndex;
use App\Models\Client;
use App\Models\SiteVisit;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiteVisitTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo(['view_site_visits', 'create_site_visits', 'edit_site_visits', 'delete_site_visits']);
    }

    public function test_a_user_without_view_permission_cannot_see_the_site_visit_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(SiteVisitIndex::class)
            ->assertForbidden();
    }

    public function test_creating_a_site_visit_assigns_a_sequential_visit_number_and_saves_rooms(): void
    {
        $client = Client::factory()->create();

        Livewire::actingAs($this->staff)->test(SiteVisitForm::class)
            ->set('client_id', (string) $client->id)
            ->set('visit_date', now()->toDateString())
            ->set('rooms.0.area_name', 'Living Room')
            ->set('rooms.0.length', '10')
            ->set('rooms.0.width', '12')
            ->call('save')
            ->assertHasNoErrors();

        $visit = SiteVisit::first();
        $this->assertNotNull($visit);
        $this->assertStringStartsWith('SV-', $visit->sv_no);
        $this->assertCount(1, $visit->items);
    }

    public function test_room_area_is_calculated_automatically_from_length_and_width(): void
    {
        $client = Client::factory()->create();

        Livewire::actingAs($this->staff)->test(SiteVisitForm::class)
            ->set('client_id', (string) $client->id)
            ->set('visit_date', now()->toDateString())
            ->set('rooms.0.area_name', 'Bedroom')
            ->set('rooms.0.length', '5')
            ->set('rooms.0.width', '4')
            ->call('save')
            ->assertHasNoErrors();

        $item = SiteVisit::first()->items->first();
        $this->assertEquals(20.00, $item->area);
    }

    public function test_a_room_can_be_added_and_removed_dynamically(): void
    {
        $client = Client::factory()->create();

        $component = Livewire::actingAs($this->staff)->test(SiteVisitForm::class)
            ->set('client_id', (string) $client->id)
            ->call('addRoom');

        $this->assertCount(2, $component->get('rooms'));

        $component->call('removeRoom', 0);
        $this->assertCount(1, $component->get('rooms'));
    }

    public function test_client_is_required_to_create_a_site_visit(): void
    {
        Livewire::actingAs($this->staff)->test(SiteVisitForm::class)
            ->set('client_id', '')
            ->set('visit_date', now()->toDateString())
            ->set('rooms.0.area_name', 'Kitchen')
            ->call('save')
            ->assertHasErrors(['client_id' => 'required']);
    }

    public function test_editing_a_site_visit_replaces_its_room_list_wholesale(): void
    {
        $client = Client::factory()->create();
        $visit = SiteVisit::factory()->create(['client_id' => $client->id]);
        $visit->items()->create(['area_name' => 'Old Room', 'length' => 1, 'width' => 1]);

        Livewire::actingAs($this->staff)->test(SiteVisitForm::class, ['siteVisit' => $visit])
            ->set('rooms.0.area_name', 'New Room')
            ->set('rooms.0.length', '3')
            ->set('rooms.0.width', '3')
            ->call('save')
            ->assertHasNoErrors();

        $visit->refresh();
        $this->assertCount(1, $visit->items);
        $this->assertSame('New Room', $visit->items->first()->area_name);
    }

    public function test_deleting_a_site_visit_requires_delete_permission(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('view_site_visits');

        $visit = SiteVisit::factory()->create();

        Livewire::actingAs($viewer)->test(SiteVisitIndex::class)
            ->call('deleteSiteVisit', $visit->id)
            ->assertForbidden();

        $this->assertNotNull($visit->fresh());
    }
}
