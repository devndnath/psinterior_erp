<?php

namespace Tests\Feature;

use App\Livewire\Handovers\Form as HandoverForm;
use App\Models\Handover;
use App\Models\Project;
use App\Models\User;
use App\Services\HandoverService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HandoverTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo(['view_handovers', 'create_handovers', 'edit_handovers', 'delete_handovers']);
    }

    public function test_a_user_without_create_handovers_permission_cannot_open_the_form(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $this->get(route('handovers.create', ['project' => $project->id]))
            ->assertForbidden();
    }

    public function test_a_staff_user_can_open_the_form_for_a_project(): void
    {
        $project = Project::factory()->create();

        // Set directly on the shared request singleton rather than via
        // Livewire::test()'s mount parameters, since Form::mount() reads
        // request()->query('project') the same way every context-only
        // Form in this codebase does (GoodsReceives/Form,
        // VendorQuotations/Form).
        request()->query->set('project', (string) $project->id);

        Livewire::actingAs($this->staff)->test(HandoverForm::class)
            ->assertSuccessful();
    }

    public function test_creating_a_second_handover_for_the_same_project_throws(): void
    {
        $project = Project::factory()->create();
        app(HandoverService::class)->create($project, ['handover_date' => now()->toDateString()]);

        $this->expectException(\RuntimeException::class);

        app(HandoverService::class)->create($project, ['handover_date' => now()->toDateString()]);
    }

    public function test_confirm_by_client_sets_the_timestamp(): void
    {
        $handover = Handover::factory()->create();
        $this->assertNull($handover->client_confirmed_at);

        $confirmed = app(HandoverService::class)->confirmByClient($handover);

        $this->assertNotNull($confirmed->client_confirmed_at);
        $this->assertTrue($confirmed->isConfirmed());
    }
}
