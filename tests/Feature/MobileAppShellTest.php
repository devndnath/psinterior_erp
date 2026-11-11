<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The phone/tablet app shell: installable manifest, bottom tab bar, folding filters, card lists. */
class MobileAppShellTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_manifest_makes_the_app_installable(): void
    {
        $this->get(route('pwa.manifest'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/manifest+json')
            ->assertJsonPath('display', 'standalone')
            ->assertJsonPath('start_url', '/dashboard')
            ->assertJsonCount(3, 'icons');

        foreach (['icons/icon-192.png', 'icons/icon-512.png', 'icons/icon-maskable-512.png', 'icons/apple-touch-icon.png', 'sw.js', 'offline.html'] as $file) {
            $this->assertFileExists(public_path($file));
        }
    }

    public function test_pages_carry_the_app_tags_and_bottom_tab_bar(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        \App\Models\Lead::factory()->create(); // so the list draws its table

        $this->actingAs($admin)->get(route('leads.index'))
            ->assertOk()
            ->assertSee(route('pwa.manifest'), false)
            ->assertSee('viewport-fit=cover', false)
            ->assertSee('class="bottom-nav', false)
            ->assertSee('filter-panel', false)
            ->assertSee('table-cards', false);
    }

    public function test_bottom_tabs_follow_permissions(): void
    {
        $sales = User::factory()->create();
        $sales->givePermissionTo('view_leads');

        $html = $this->actingAs($sales)->get(route('dashboard'))->assertOk()->getContent();
        $nav = substr($html, strpos($html, 'class="bottom-nav'));

        $this->assertStringContainsString(route('crm.overview'), $nav);
        $this->assertStringContainsString(route('calls.index'), $nav);
        $this->assertStringNotContainsString(route('projects.index'), $nav);
        $this->assertStringNotContainsString(route('invoices.index'), $nav);
    }
}
