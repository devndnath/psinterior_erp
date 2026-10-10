<?php

namespace Tests\Feature;

use App\Enums\ServiceRequestStatus;
use App\Livewire\ServiceRequests\Form as ServiceRequestForm;
use App\Livewire\ServiceRequests\Show as ServiceRequestShow;
use App\Models\Client;
use App\Models\Project;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Warranty;
use App\Services\ServiceRequestService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    /**
     * The whole point of this phase's permission-reuse decision
     * (ServiceRequestPolicy): a user holds ONLY view/create/edit
     * `*_warranties` — none of the never-seeded `service_requests`
     * strings, because they don't exist — and must still be able to
     * fully create and progress a Service Request through its status
     * chain.
     */
    public function test_a_user_with_only_warranties_permissions_can_fully_use_service_requests(): void
    {
        $warrantyStyleUser = User::factory()->create();
        $warrantyStyleUser->givePermissionTo(['view_warranties', 'create_warranties', 'edit_warranties']);

        $client = Client::factory()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);
        $warranty = Warranty::factory()->create(['project_id' => $project->id]);

        // Set directly on the shared request singleton rather than via
        // Livewire::test()'s mount parameters, since Form::mount() reads
        // request()->query('warranty') the same way every context-only
        // Form in this codebase does (GoodsReceives/Form,
        // VendorQuotations/Form).
        request()->query->set('warranty', (string) $warranty->id);

        Livewire::actingAs($warrantyStyleUser)->test(ServiceRequestForm::class)
            ->set('client_id', (string) $client->id)
            ->set('complaint', 'Leaking pipe under the sink')
            ->call('save')
            ->assertHasNoErrors();

        $sr = ServiceRequest::first();
        $this->assertNotNull($sr);
        $this->assertSame(ServiceRequestStatus::Open, $sr->status);

        $technician = User::factory()->create();

        Livewire::actingAs($warrantyStyleUser)->test(ServiceRequestShow::class, ['serviceRequest' => $sr])
            ->set('assign_user_id', (string) $technician->id)
            ->call('assignTechnician')
            ->assertHasNoErrors()
            ->call('scheduleSiteVisit')
            ->assertHasNoErrors()
            ->call('markRepair')
            ->assertHasNoErrors()
            ->call('markVerification')
            ->assertHasNoErrors()
            ->call('close')
            ->assertHasNoErrors();

        $this->assertSame(ServiceRequestStatus::Closed, $sr->fresh()->status);
    }

    public function test_a_user_without_view_warranties_permission_cannot_view_a_service_request(): void
    {
        $user = User::factory()->create();
        $sr = ServiceRequest::factory()->create();

        Livewire::actingAs($user)->test(ServiceRequestShow::class, ['serviceRequest' => $sr])
            ->assertForbidden();
    }

    public function test_the_status_chain_works_end_to_end_via_the_service(): void
    {
        $warranty = Warranty::factory()->create();
        $service = app(ServiceRequestService::class);

        $sr = $service->create($warranty, ['client_id' => Client::factory()->create()->id, 'complaint' => 'Squeaky door']);
        $this->assertStringStartsWith('SR-', $sr->sr_no);
        $this->assertSame(ServiceRequestStatus::Open, $sr->status);

        $technician = User::factory()->create();

        $sr = $service->assignTechnician($sr, $technician->id);
        $this->assertSame(ServiceRequestStatus::TechnicianAssigned, $sr->status);

        $sr = $service->scheduleSiteVisit($sr);
        $this->assertSame(ServiceRequestStatus::SiteVisit, $sr->status);

        $sr = $service->markRepair($sr);
        $this->assertSame(ServiceRequestStatus::Repair, $sr->status);

        $sr = $service->markVerification($sr);
        $this->assertSame(ServiceRequestStatus::Verification, $sr->status);

        $sr = $service->close($sr);
        $this->assertSame(ServiceRequestStatus::Closed, $sr->status);
    }

    public function test_an_invalid_transition_throws(): void
    {
        $sr = ServiceRequest::factory()->create(['status' => ServiceRequestStatus::Open->value]);

        $this->expectException(\RuntimeException::class);

        app(ServiceRequestService::class)->markRepair($sr);
    }
}
