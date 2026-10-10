<?php

namespace Tests\Feature;

use App\Livewire\Vendors\Form as VendorForm;
use App\Livewire\Vendors\Index as VendorIndex;
use App\Models\Vendor;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VendorTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo(['view_vendors', 'create_vendors', 'edit_vendors', 'delete_vendors']);
    }

    public function test_a_user_without_view_permission_cannot_see_the_vendor_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(VendorIndex::class)
            ->assertForbidden();
    }

    public function test_creating_a_vendor_assigns_a_sequential_number(): void
    {
        Livewire::actingAs($this->staff)->test(VendorForm::class)
            ->set('company_name', 'Acme Hardware')
            ->set('category', 'Hardware')
            ->call('save')
            ->assertHasNoErrors();

        $vendor = Vendor::first();
        $this->assertNotNull($vendor);
        $this->assertStringStartsWith('VEN-', $vendor->vendor_code);
        $this->assertSame('Acme Hardware', $vendor->company_name);
    }

    public function test_creating_a_second_vendor_issues_the_next_sequential_number(): void
    {
        Livewire::actingAs($this->staff)->test(VendorForm::class)
            ->set('company_name', 'Vendor One')
            ->call('save');

        Livewire::actingAs($this->staff)->test(VendorForm::class)
            ->set('company_name', 'Vendor Two')
            ->call('save');

        $codes = Vendor::orderBy('id')->pluck('vendor_code')->all();
        $this->assertCount(2, $codes);
        $this->assertNotSame($codes[0], $codes[1]);
    }

    public function test_deleting_a_vendor_requires_delete_permission(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('view_vendors');

        $vendor = Vendor::factory()->create();

        Livewire::actingAs($viewer)->test(VendorIndex::class)
            ->call('deleteVendor', $vendor->id)
            ->assertForbidden();

        $this->assertNotNull($vendor->fresh());
    }
}
