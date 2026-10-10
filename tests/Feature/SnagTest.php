<?php

namespace Tests\Feature;

use App\Enums\SnagStatus;
use App\Livewire\Snags\Index as SnagIndex;
use App\Models\Project;
use App\Models\Snag;
use App\Models\User;
use App\Services\SnagService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SnagTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo(['view_snags', 'create_snags', 'edit_snags', 'delete_snags']);
    }

    public function test_a_user_without_view_snags_permission_cannot_see_the_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(SnagIndex::class)
            ->assertForbidden();
    }

    public function test_creating_a_snag_assigns_a_sequential_number_and_pending_status(): void
    {
        $project = Project::factory()->create();

        $snag = app(SnagService::class)->create($project, ['issue' => 'Crack in wall']);

        $this->assertStringStartsWith('SNAG-', $snag->snag_no);
        $this->assertSame(SnagStatus::Pending, $snag->status);
    }

    public function test_the_full_status_chain_works_via_the_service(): void
    {
        $project = Project::factory()->create();
        $technician = User::factory()->create();
        $service = app(SnagService::class);

        $snag = $service->create($project, ['issue' => 'Crack in wall']);

        $snag = $service->assign($snag, $technician->id);
        $this->assertSame(SnagStatus::Assigned, $snag->status);
        $this->assertSame($technician->id, $snag->assigned_to);

        $snag = $service->startProgress($snag);
        $this->assertSame(SnagStatus::InProgress, $snag->status);

        $snag = $service->markFixed($snag);
        $this->assertSame(SnagStatus::Fixed, $snag->status);

        $snag = $service->verify($snag);
        $this->assertSame(SnagStatus::Verified, $snag->status);

        $snag = $service->close($snag);
        $this->assertSame(SnagStatus::Closed, $snag->status);
    }

    public function test_an_invalid_transition_throws(): void
    {
        $snag = Snag::factory()->create(['status' => SnagStatus::Pending->value]);

        $this->expectException(\RuntimeException::class);

        app(SnagService::class)->verify($snag);
    }

    public function test_reopen_works_from_any_non_closed_state(): void
    {
        $snag = Snag::factory()->create(['status' => SnagStatus::Fixed->value]);

        $reopened = app(SnagService::class)->reopen($snag);

        $this->assertSame(SnagStatus::Pending, $reopened->status);
    }

    public function test_reopen_throws_once_closed(): void
    {
        $snag = Snag::factory()->create(['status' => SnagStatus::Closed->value]);

        $this->expectException(\RuntimeException::class);

        app(SnagService::class)->reopen($snag);
    }
}
