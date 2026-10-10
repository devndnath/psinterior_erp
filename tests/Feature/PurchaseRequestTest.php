<?php

namespace Tests\Feature;

use App\Enums\PurchaseRequestStatus;
use App\Livewire\PurchaseRequests\Form as PurchaseRequestForm;
use App\Livewire\PurchaseRequests\Index as PurchaseRequestIndex;
use App\Livewire\PurchaseRequests\Show as PurchaseRequestShow;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Services\PurchaseRequestService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo(['view_purchase_orders', 'create_purchase_orders', 'edit_purchase_orders', 'delete_purchase_orders']);
    }

    /**
     * The whole point of the permission-gating pitfall fix: a user has
     * only the never-anticipated `purchase_requests` permissions missing
     * (they don't exist), but holds the real, seeded
     * `*_purchase_orders` abilities and must still be able to use this
     * module end to end.
     */
    public function test_a_user_with_only_purchase_orders_permissions_can_use_purchase_requests(): void
    {
        $procurementStyleUser = User::factory()->create();
        $procurementStyleUser->givePermissionTo(['view_purchase_orders', 'create_purchase_orders', 'edit_purchase_orders']);

        $project = Project::factory()->create();
        $product = Product::factory()->create();

        Livewire::actingAs($procurementStyleUser)->test(PurchaseRequestIndex::class)
            ->assertSuccessful();

        Livewire::actingAs($procurementStyleUser)->test(PurchaseRequestForm::class)
            ->set('project_id', (string) $project->id)
            ->set('items.0.product_id', (string) $product->id)
            ->set('items.0.quantity', '10')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(1, PurchaseRequest::count());
    }

    public function test_a_user_without_view_purchase_orders_permission_cannot_see_the_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(PurchaseRequestIndex::class)
            ->assertForbidden();
    }

    public function test_creating_a_purchase_request_assigns_a_sequential_number_and_draft_status(): void
    {
        $project = Project::factory()->create();
        $product = Product::factory()->create();

        Livewire::actingAs($this->staff)->test(PurchaseRequestForm::class)
            ->set('project_id', (string) $project->id)
            ->set('items.0.product_id', (string) $product->id)
            ->set('items.0.quantity', '5')
            ->call('save')
            ->assertHasNoErrors();

        $pr = PurchaseRequest::first();
        $this->assertNotNull($pr);
        $this->assertStringStartsWith('PR-', $pr->pr_no);
        $this->assertSame(PurchaseRequestStatus::Draft, $pr->status);
        $this->assertSame(1, $pr->items()->count());
    }

    public function test_sending_a_draft_request_for_quotation_transitions_its_status(): void
    {
        $pr = PurchaseRequest::factory()->create(['status' => PurchaseRequestStatus::Draft->value]);

        Livewire::actingAs($this->staff)->test(PurchaseRequestShow::class, ['purchaseRequest' => $pr])
            ->call('sendForQuotation')
            ->assertHasNoErrors();

        $this->assertSame(PurchaseRequestStatus::SentForQuotation, $pr->fresh()->status);
    }

    public function test_updating_a_request_is_refused_once_it_has_left_draft(): void
    {
        $pr = PurchaseRequest::factory()->create(['status' => PurchaseRequestStatus::SentForQuotation->value]);
        $product = Product::factory()->create();

        $this->expectException(\RuntimeException::class);

        app(PurchaseRequestService::class)->update($pr, [], [
            ['product_id' => $product->id, 'quantity' => 1],
        ]);
    }
}
