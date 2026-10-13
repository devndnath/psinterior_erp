<?php

namespace Tests\Feature;

use App\Livewire\Client\SiteReports\Index as ClientSiteReportIndex;
use App\Livewire\Client\SiteReports\Show as ClientSiteReportShow;
use App\Livewire\SiteReports\Form as SiteReportForm;
use App\Livewire\SiteReports\Index as SiteReportIndex;
use App\Models\Client;
use App\Models\Project;
use App\Models\SiteReport;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Covers the Phase 17g "Daily Site Report / Site Diary" fix-first item: a
 * recurring, project-scoped field log (weather, manpower, work carried
 * out, issues) with its own full CRUD permission module — unlike
 * ContractItem/PaymentScheduleLine, a report isn't a tight sub-resource of
 * a single parent aggregate, it's filed independently by field staff, so
 * it gets its own SiteReportPolicy and site_reports permission set.
 */
class SiteReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        Storage::fake('uploads');

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo(['view_site_reports', 'create_site_reports', 'edit_site_reports', 'delete_site_reports']);
    }

    protected function makeClientUser(Client $client): User
    {
        $user = User::factory()->client()->create();
        $client->update(['user_id' => $user->id]);

        return $user;
    }

    public function test_a_user_without_view_permission_cannot_see_the_site_report_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(SiteReportIndex::class)
            ->assertForbidden();
    }

    public function test_creating_a_site_report_saves_the_filer_and_fields(): void
    {
        $project = Project::factory()->create();

        Livewire::actingAs($this->staff)->test(SiteReportForm::class)
            ->set('project_id', (string) $project->id)
            ->set('report_date', now()->toDateString())
            ->set('weather', 'Sunny')
            ->set('manpower_count', '12')
            ->set('work_summary', 'Poured the ground floor slab and started column formwork.')
            ->call('save')
            ->assertHasNoErrors();

        $report = SiteReport::first();
        $this->assertNotNull($report);
        $this->assertSame($project->id, $report->project_id);
        $this->assertSame($this->staff->id, $report->reported_by);
        $this->assertSame('Sunny', $report->weather);
        $this->assertSame(12, $report->manpower_count);
    }

    public function test_work_summary_and_project_are_required(): void
    {
        Livewire::actingAs($this->staff)->test(SiteReportForm::class)
            ->set('report_date', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['project_id' => 'required', 'work_summary' => 'required']);
    }

    public function test_adding_a_report_stores_uploaded_photos(): void
    {
        $project = Project::factory()->create();
        $photo = UploadedFile::fake()->image('progress.jpg');

        Livewire::actingAs($this->staff)->test(SiteReportForm::class)
            ->set('project_id', (string) $project->id)
            ->set('report_date', now()->toDateString())
            ->set('work_summary', 'Tiling started in the master bathroom.')
            ->set('photos', [$photo])
            ->call('save')
            ->assertHasNoErrors();

        $report = SiteReport::first();
        $this->assertSame(1, $report->documents()->where('category', 'site_report_photo')->count());
    }

    public function test_editing_a_report_does_not_change_its_project(): void
    {
        $project = Project::factory()->create();
        $otherProject = Project::factory()->create();
        $report = SiteReport::factory()->for($project)->create(['work_summary' => 'Original summary.']);

        Livewire::actingAs($this->staff)->test(SiteReportForm::class, ['siteReport' => $report])
            ->set('project_id', (string) $otherProject->id) // disabled in the UI, but even if forced...
            ->set('work_summary', 'Updated summary — second coat of plaster applied.')
            ->call('save')
            ->assertHasNoErrors();

        $report->refresh();
        $this->assertSame($project->id, $report->project_id);
        $this->assertSame('Updated summary — second coat of plaster applied.', $report->work_summary);
    }

    public function test_deleting_a_report_requires_delete_permission(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('view_site_reports');

        $report = SiteReport::factory()->create();

        Livewire::actingAs($viewer)->test(SiteReportIndex::class)
            ->call('deleteSiteReport', $report->id)
            ->assertForbidden();

        $this->assertNotNull($report->fresh());
    }

    public function test_a_client_only_sees_site_reports_for_their_own_projects(): void
    {
        $client = Client::factory()->create();
        $otherClient = Client::factory()->create();
        $clientUser = $this->makeClientUser($client);

        $ownProject = Project::factory()->create(['client_id' => $client->id]);
        $foreignProject = Project::factory()->create(['client_id' => $otherClient->id]);

        $ownReport = SiteReport::factory()->for($ownProject)->create();
        SiteReport::factory()->for($foreignProject)->create();

        $component = Livewire::actingAs($clientUser, 'client')->test(ClientSiteReportIndex::class);

        $this->assertSame(1, $component->get('reports')->total());
        $this->assertTrue($ownReport->exists());
    }

    public function test_a_client_cannot_view_another_clients_site_report(): void
    {
        $client = Client::factory()->create();
        $otherClient = Client::factory()->create();
        $clientUser = $this->makeClientUser($client);

        $foreignProject = Project::factory()->create(['client_id' => $otherClient->id]);
        $foreignReport = SiteReport::factory()->for($foreignProject)->create();

        Livewire::actingAs($clientUser, 'client')->test(ClientSiteReportShow::class, ['siteReport' => $foreignReport])
            ->assertForbidden();
    }
}
