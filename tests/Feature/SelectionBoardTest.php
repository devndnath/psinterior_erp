<?php

namespace Tests\Feature;

use App\Livewire\Client\Selections\Show as ClientSelectionShow;
use App\Livewire\MaterialSelections\Form as SelectionForm;
use App\Livewire\MaterialSelections\Index as SelectionIndex;
use App\Livewire\MaterialSelections\Show as SelectionShow;
use App\Models\Client;
use App\Models\Project;
use App\Models\SelectionBoard;
use App\Models\SelectionOption;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Covers the Phase 17e "material/finish selection board" fix-first item
 * — the audit's "single biggest domain-specific gap": a structured
 * record of which tile/paint/hardware option a client picked, instead of
 * ad-hoc notes or emails. Exercises the full lifecycle (Draft -> Sent ->
 * Decided, or Cancelled) and both ways a decision gets recorded: the
 * client's own portal pick and staff recording it on the client's
 * behalf, mirroring the dual-caller pattern already proven for Designs.
 */
class SelectionBoardTest extends TestCase
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
            'view_material_selections', 'create_material_selections',
            'edit_material_selections', 'delete_material_selections',
            'decide_material_selection',
        ]);
    }

    protected function makeClientUser(Client $client): User
    {
        $user = User::factory()->client()->create();
        $client->update(['user_id' => $user->id]);

        return $user;
    }

    public function test_a_user_without_view_permission_cannot_see_the_selection_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(SelectionIndex::class)
            ->assertForbidden();
    }

    public function test_creating_a_selection_assigns_a_sequential_board_number(): void
    {
        $project = Project::factory()->create();

        Livewire::actingAs($this->staff)->test(SelectionForm::class)
            ->set('project_id', (string) $project->id)
            ->set('area', 'Master Bath')
            ->set('title', 'Floor Tile')
            ->call('save')
            ->assertHasNoErrors();

        $board = SelectionBoard::first();
        $this->assertNotNull($board);
        $this->assertStringStartsWith('SEL-', $board->board_no);
        $this->assertSame('draft', $board->status->value);
    }

    public function test_adding_an_option_stores_its_reference_image(): void
    {
        $board = SelectionBoard::factory()->create();
        $image = UploadedFile::fake()->image('tile.jpg');

        Livewire::actingAs($this->staff)->test(SelectionShow::class, ['board' => $board])
            ->call('toggleOptionForm')
            ->set('optionLabel', 'Option A — Carrara Marble')
            ->set('optionPrice', '1200.50')
            ->set('optionImage', $image)
            ->call('addOption')
            ->assertHasNoErrors();

        $option = $board->options()->first();
        $this->assertSame('Option A — Carrara Marble', $option->label);
        $this->assertNotNull($option->file_document_id);
        $this->assertNotNull($option->file);
    }

    public function test_sending_a_board_requires_at_least_two_options(): void
    {
        $board = SelectionBoard::factory()->create();
        SelectionOption::factory()->for($board, 'board')->create();

        Livewire::actingAs($this->staff)->test(SelectionShow::class, ['board' => $board])
            ->call('sendToClient')
            ->assertHasErrors();

        $this->assertSame('draft', $board->fresh()->status->value);

        SelectionOption::factory()->for($board, 'board')->create();

        Livewire::actingAs($this->staff)->test(SelectionShow::class, ['board' => $board])
            ->call('sendToClient')
            ->assertHasNoErrors();

        $this->assertSame('sent', $board->fresh()->status->value);
    }

    public function test_options_cannot_be_added_once_a_board_has_been_sent(): void
    {
        $board = SelectionBoard::factory()->create(['status' => 'sent']);
        SelectionOption::factory()->for($board, 'board')->count(2)->create();

        Livewire::actingAs($this->staff)->test(SelectionShow::class, ['board' => $board])
            ->call('toggleOptionForm')
            ->set('optionLabel', 'Late Option')
            ->call('addOption')
            ->assertHasErrors();

        $this->assertSame(2, $board->options()->count());
    }

    public function test_staff_can_record_a_decision_on_the_clients_behalf(): void
    {
        $board = SelectionBoard::factory()->create(['status' => 'sent']);
        $optionA = SelectionOption::factory()->for($board, 'board')->create();
        SelectionOption::factory()->for($board, 'board')->create();

        Livewire::actingAs($this->staff)->test(SelectionShow::class, ['board' => $board])
            ->call('openDecisionForm', $optionA->id)
            ->set('decisionComment', 'Client confirmed by phone.')
            ->call('recordDecision')
            ->assertHasNoErrors();

        $board->refresh();
        $this->assertSame('decided', $board->status->value);
        $this->assertSame($optionA->id, $board->decided_option_id);
        $this->assertSame('Client confirmed by phone.', $board->client_comment);
    }

    public function test_a_decision_cannot_be_recorded_before_the_board_is_sent(): void
    {
        $board = SelectionBoard::factory()->create(['status' => 'draft']);
        $option = SelectionOption::factory()->for($board, 'board')->create();

        Livewire::actingAs($this->staff)->test(SelectionShow::class, ['board' => $board])
            ->call('openDecisionForm', $option->id)
            ->call('recordDecision')
            ->assertHasErrors();

        $this->assertSame('draft', $board->fresh()->status->value);
    }

    public function test_a_user_without_decide_permission_cannot_record_a_decision(): void
    {
        $limitedUser = User::factory()->create();
        $limitedUser->givePermissionTo(['view_material_selections', 'edit_material_selections']);

        $board = SelectionBoard::factory()->create(['status' => 'sent']);
        $option = SelectionOption::factory()->for($board, 'board')->create();

        Livewire::actingAs($limitedUser)->test(SelectionShow::class, ['board' => $board])
            ->call('openDecisionForm', $option->id)
            ->assertForbidden();
    }

    public function test_a_draft_or_sent_board_can_be_cancelled_but_a_decided_one_cannot(): void
    {
        $board = SelectionBoard::factory()->create(['status' => 'sent']);

        Livewire::actingAs($this->staff)->test(SelectionShow::class, ['board' => $board])
            ->call('cancelBoard')
            ->assertHasNoErrors();

        $this->assertSame('cancelled', $board->fresh()->status->value);

        $decided = SelectionBoard::factory()->create(['status' => 'decided']);

        Livewire::actingAs($this->staff)->test(SelectionShow::class, ['board' => $decided])
            ->call('cancelBoard')
            ->assertHasErrors();

        $this->assertSame('decided', $decided->fresh()->status->value);
    }

    public function test_a_client_can_record_their_own_choice_but_not_on_another_clients_board(): void
    {
        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();
        $userA = $this->makeClientUser($clientA);

        $projectA = Project::factory()->create(['client_id' => $clientA->id]);
        $projectB = Project::factory()->create(['client_id' => $clientB->id]);

        $boardA = SelectionBoard::factory()->create(['project_id' => $projectA->id, 'status' => 'sent']);
        $optionA = SelectionOption::factory()->for($boardA, 'board')->create();
        SelectionOption::factory()->for($boardA, 'board')->create();

        $boardB = SelectionBoard::factory()->create(['project_id' => $projectB->id, 'status' => 'sent']);
        SelectionOption::factory()->for($boardB, 'board')->count(2)->create();

        Livewire::actingAs($userA, 'client')->test(ClientSelectionShow::class, ['board' => $boardA])
            ->assertOk()
            ->call('chooseOption', $optionA->id)
            ->set('comment', 'Love this one!')
            ->call('confirmChoice')
            ->assertHasNoErrors();

        $boardA->refresh();
        $this->assertSame('decided', $boardA->status->value);
        $this->assertSame($optionA->id, $boardA->decided_option_id);
        $this->assertSame('Love this one!', $boardA->client_comment);

        Livewire::actingAs($userA, 'client')->test(ClientSelectionShow::class, ['board' => $boardB])
            ->assertForbidden();
    }

    public function test_a_draft_board_is_not_reachable_from_the_client_portal(): void
    {
        $client = Client::factory()->create();
        $user = $this->makeClientUser($client);
        $project = Project::factory()->create(['client_id' => $client->id]);
        $board = SelectionBoard::factory()->create(['project_id' => $project->id, 'status' => 'draft']);

        Livewire::actingAs($user, 'client')->test(ClientSelectionShow::class, ['board' => $board])
            ->assertStatus(404);
    }
}
