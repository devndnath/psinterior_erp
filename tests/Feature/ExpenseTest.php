<?php

namespace Tests\Feature;

use App\Livewire\Expenses\Form as ExpenseForm;
use App\Livewire\Expenses\Index as ExpenseIndex;
use App\Livewire\Expenses\Show as ExpenseShow;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo([
            'view_expenses', 'create_expenses', 'edit_expenses', 'delete_expenses',
        ]);
    }

    public function test_a_user_without_view_permission_cannot_see_the_expense_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(ExpenseIndex::class)
            ->assertForbidden();
    }

    public function test_creating_an_expense_assigns_a_sequential_number(): void
    {
        $project = Project::factory()->create();
        $category = ExpenseCategory::factory()->create();

        Livewire::actingAs($this->staff)->test(ExpenseForm::class)
            ->set('project_id', (string) $project->id)
            ->set('category_id', (string) $category->id)
            ->set('vendor_name', 'Acme Supplies')
            ->set('amount', '5000')
            ->set('date', now()->toDateString())
            ->set('method', 'cash')
            ->call('save')
            ->assertHasNoErrors();

        $expense = Expense::first();
        $this->assertNotNull($expense);
        $this->assertStringStartsWith('EXP-', $expense->expense_no);
        $this->assertEquals(5000, $expense->amount);
    }

    public function test_deleting_an_expense_requires_delete_permission(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('view_expenses');

        $project = Project::factory()->create();
        $expense = Expense::factory()->for($project)->create();

        Livewire::actingAs($viewer)->test(ExpenseShow::class, ['expense' => $expense])
            ->call('deleteExpense')
            ->assertForbidden();

        $this->assertNotNull($expense->fresh());
    }
}
