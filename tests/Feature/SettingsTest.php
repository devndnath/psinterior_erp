<?php

namespace Tests\Feature;

use App\Livewire\Settings\Index as SettingsIndex;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('manage_settings');
    }

    public function test_saving_company_details_persists_to_settings_table(): void
    {
        Livewire::actingAs($this->admin)
            ->test(SettingsIndex::class)
            ->set('company_name', 'Acme Interiors Ltd')
            ->set('company_email', 'hello@acme.test')
            ->call('saveCompany')
            ->assertHasNoErrors();

        $this->assertSame('Acme Interiors Ltd', Setting::get('company.name'));
        $this->assertSame('hello@acme.test', Setting::get('company.email'));
    }

    public function test_invalid_company_email_is_rejected(): void
    {
        Livewire::actingAs($this->admin)
            ->test(SettingsIndex::class)
            ->set('company_email', 'not-an-email')
            ->call('saveCompany')
            ->assertHasErrors(['company_email']);
    }

    public function test_saving_finance_settings_persists_tax_rate_and_currency(): void
    {
        Livewire::actingAs($this->admin)
            ->test(SettingsIndex::class)
            ->set('currency', 'usd')
            ->set('tax_rate', '7.5')
            ->call('saveFinance')
            ->assertHasNoErrors();

        $this->assertSame('USD', Setting::get('finance.currency'));
        $this->assertEquals(7.5, Setting::get('finance.tax_rate'));
    }

    public function test_tax_rate_over_100_is_rejected(): void
    {
        Livewire::actingAs($this->admin)
            ->test(SettingsIndex::class)
            ->set('tax_rate', '150')
            ->call('saveFinance')
            ->assertHasErrors(['tax_rate']);
    }

    public function test_saving_prefixes_updates_the_numbering_sequence_prefix(): void
    {
        Livewire::actingAs($this->admin)
            ->test(SettingsIndex::class)
            ->set('prefix_invoice', 'INVOICE')
            ->call('savePrefixes')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('document_sequences', ['key' => 'INV', 'prefix' => 'INVOICE']);
    }

    public function test_user_without_permission_cannot_open_settings_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('settings.index'))->assertForbidden();
    }
}
