<?php

namespace Tests\Feature;

use App\Livewire\Schedule\Popup;
use App\Models\Client;
use App\Models\Meeting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** The top bar's "My schedule" popup. */
class SchedulePopupTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_it_queries_nothing_until_opened_then_lists_the_coming_days(): void
    {
        Client::factory()->create(['name' => 'Rahim Uddin'])->followups()
            ->create(['date' => today()->toDateString(), 'time' => '11:30', 'type' => 'call']);
        Meeting::factory()->create(['date' => today()->addDays(20)->toDateString(), 'time' => '15:00']);

        Livewire::actingAs($this->admin)->test(Popup::class)
            ->assertViewHas('groups', fn ($g) => $g->isEmpty())   // closed: nothing loaded
            ->call('load')
            ->assertSee('Rahim Uddin')->assertSee('11:30')->assertSee('Today')
            ->assertViewHas('groups', fn ($g) => $g->flatten(1)->where('type', 'Meeting')->isEmpty())
            ->call('setDays', 30)
            ->assertViewHas('groups', fn ($g) => $g->flatten(1)->where('type', 'Meeting')->count() === 1);
    }

    public function test_the_button_is_in_the_top_bar(): void
    {
        $this->actingAs($this->admin)->get(route('dashboard'))
            ->assertOk()->assertSee('aria-label="My schedule"', false)->assertSee('Open full calendar');
    }
}
