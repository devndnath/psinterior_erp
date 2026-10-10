<?php

namespace Tests\Feature;

use App\Enums\UserType;
use App\Livewire\Client\Designs\Show as ClientDesignShow;
use App\Livewire\Client\Projects\Show as ClientProjectShow;
use App\Livewire\Client\Quotations\Show as ClientQuotationShow;
use App\Models\Client;
use App\Models\Design;
use App\Models\DesignRevision;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The critical test for this phase (see "Portal authorization model"
 * in PHASE11_SUMMARY.md): proves the plain ownership-check pattern
 * actually works for the positive case (a client CAN act on their own
 * record) and not only the negative case (a different client's record
 * is refused) — a bug that routed this through a staff Policy would
 * make BOTH cases fail 403, which would look like it "worked" if only
 * the negative case were asserted.
 */
class ClientPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function makeClientUser(Client $client): User
    {
        $user = User::factory()->client()->create();
        $client->update(['user_id' => $user->id]);

        return $user;
    }

    public function test_a_client_can_view_their_own_project_but_not_another_clients_project(): void
    {
        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();
        $userA = $this->makeClientUser($clientA);

        $projectA = Project::factory()->create(['client_id' => $clientA->id]);
        $projectB = Project::factory()->create(['client_id' => $clientB->id]);

        Livewire::actingAs($userA, 'client')->test(ClientProjectShow::class, ['project' => $projectA])
            ->assertOk()
            ->assertSee($projectA->project_code);

        Livewire::actingAs($userA, 'client')->test(ClientProjectShow::class, ['project' => $projectB])
            ->assertForbidden();
    }

    public function test_a_client_can_record_a_decision_on_their_own_design_but_not_another_clients_design(): void
    {
        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();
        $userA = $this->makeClientUser($clientA);

        $projectA = Project::factory()->create(['client_id' => $clientA->id]);
        $projectB = Project::factory()->create(['client_id' => $clientB->id]);

        $designA = Design::factory()->create(['project_id' => $projectA->id]);
        DesignRevision::factory()->create(['design_id' => $designA->id, 'version' => 1]);

        $designB = Design::factory()->create(['project_id' => $projectB->id]);
        DesignRevision::factory()->create(['design_id' => $designB->id, 'version' => 1]);

        Livewire::actingAs($userA, 'client')->test(ClientDesignShow::class, ['design' => $designA])
            ->assertOk()
            ->set('decision', 'approved')
            ->call('recordDecision')
            ->assertHasNoErrors();

        $this->assertSame('approved', $designA->fresh()->status->value);

        Livewire::actingAs($userA, 'client')->test(ClientDesignShow::class, ['design' => $designB])
            ->assertForbidden();
    }

    public function test_a_client_can_record_a_decision_on_their_own_quotation_but_not_another_clients_quotation(): void
    {
        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();
        $userA = $this->makeClientUser($clientA);

        $quotationA = Quotation::factory()->create(['client_id' => $clientA->id, 'status' => 'sent']);
        $quotationB = Quotation::factory()->create(['client_id' => $clientB->id, 'status' => 'sent']);

        Livewire::actingAs($userA, 'client')->test(ClientQuotationShow::class, ['quotation' => $quotationA])
            ->assertOk()
            ->set('decision', 'approved')
            ->call('recordDecision')
            ->assertHasNoErrors();

        $this->assertSame('approved', $quotationA->fresh()->status->value);

        Livewire::actingAs($userA, 'client')->test(ClientQuotationShow::class, ['quotation' => $quotationB])
            ->assertForbidden();
    }

    public function test_a_client_portal_user_holds_no_spatie_permissions(): void
    {
        $client = Client::factory()->create();
        $user = $this->makeClientUser($client);

        $this->assertSame(UserType::Client, $user->user_type);
        $this->assertCount(0, $user->getAllPermissions());
    }
}
