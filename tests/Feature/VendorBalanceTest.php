<?php

namespace Tests\Feature;

use App\Livewire\Vendors\Index as VendorIndex;
use App\Models\Expense;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Vendor list money: payable (purchase orders placed), paid (expenses
 * against the vendor), due, the summary above the list, and filtering
 * and sorting by them.
 */
class VendorBalanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['view_vendors', 'view_finance']);
    }

    /** owes: 100k ordered, 40k paid. clear: 50k/50k. ahead: 10k/15k. idle: nothing. */
    private function vendors(): array
    {
        $make = function (string $name, float $ordered, float $paid) {
            $v = Vendor::factory()->create(['company_name' => $name]);
            if ($ordered) {
                PurchaseOrder::factory()->create(['vendor_id' => $v->id, 'status' => 'sent', 'total' => $ordered]);
            }
            if ($paid) {
                Expense::factory()->create(['vendor_id' => $v->id, 'amount' => $paid]);
            }

            return $v;
        };
        // Drafts and cancelled orders never count as payable.
        $owes = $make('Owes Co', 100000, 40000);
        PurchaseOrder::factory()->create(['vendor_id' => $owes->id, 'status' => 'draft', 'total' => 999999]);
        PurchaseOrder::factory()->create(['vendor_id' => $owes->id, 'status' => 'cancelled', 'total' => 999999]);

        return [$owes, $make('Clear Co', 50000, 50000), $make('Ahead Co', 10000, 15000), $make('Idle Co', 0, 0)];
    }

    public function test_columns_and_summary(): void
    {
        [$owes] = $this->vendors();

        $view = Livewire::actingAs($this->user)->test(VendorIndex::class)->assertOk();

        $row = $view->viewData('vendors')->firstWhere('id', $owes->id);
        $this->assertEquals(100000, $row->payable_sum);
        $this->assertEquals(40000, $row->paid_sum);

        $s = $view->viewData('summary');
        $this->assertSame(4, $s['vendors']);
        $this->assertEquals(160000, $s['payable']);
        $this->assertEquals(105000, $s['paid']);
        $this->assertEquals(60000, $s['due']);
        $this->assertSame(1, $s['withDue']);
        $this->assertEquals(5000, $s['advance']);

        $view->assertSee('60,000.00')->assertSee('Advance');
    }

    public function test_filter_by_payment_status_and_sort_by_due(): void
    {
        $this->vendors();
        $names = fn (array $q) => Livewire::withQueryParams($q)->actingAs($this->user)
            ->test(VendorIndex::class)->viewData('vendors')->pluck('company_name')->all();

        $this->assertSame(['Owes Co'], $names(['balance' => 'due']));
        $this->assertSame(['Clear Co'], $names(['balance' => 'paid']));
        $this->assertSame(['Ahead Co'], $names(['balance' => 'advance']));
        $this->assertSame(['Idle Co'], $names(['balance' => 'none']));
        $this->assertSame('Owes Co', $names(['sort' => 'due'])[0]);
    }

    public function test_money_is_hidden_without_finance_or_purchasing_permission(): void
    {
        $this->vendors();
        $plain = User::factory()->create();
        $plain->givePermissionTo('view_vendors');

        $view = Livewire::actingAs($plain)->test(VendorIndex::class)->assertOk();
        $this->assertNull($view->viewData('summary'));
        $view->assertDontSee('Payable');
    }
}
