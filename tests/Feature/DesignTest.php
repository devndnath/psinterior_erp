<?php

namespace Tests\Feature;

use App\Enums\ApprovalDecision;
use App\Enums\DesignRevisionStatus;
use App\Enums\DesignStatus;
use App\Livewire\Designs\Form as DesignForm;
use App\Livewire\Designs\Index as DesignIndex;
use App\Livewire\Designs\Show as DesignShow;
use App\Models\Design;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DesignTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        Storage::fake('uploads');

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo([
            'view_designs', 'create_designs', 'edit_designs', 'delete_designs',
            'upload_design', 'approve_design',
        ]);
    }

    public function test_a_user_without_view_permission_cannot_see_the_design_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(DesignIndex::class)
            ->assertForbidden();
    }

    public function test_creating_a_design_assigns_a_sequential_design_number_and_stores_the_first_revision(): void
    {
        $project = Project::factory()->create();
        $file = UploadedFile::fake()->create('floor-plan.pdf', 500, 'application/pdf');

        Livewire::actingAs($this->staff)->test(DesignForm::class)
            ->set('project_id', (string) $project->id)
            ->set('title', 'Living Room Layout')
            ->set('category', 'floor_plan')
            ->set('file', $file)
            ->call('save')
            ->assertHasNoErrors();

        $design = Design::first();
        $this->assertNotNull($design);
        $this->assertStringStartsWith('DSG-', $design->design_no);

        // The first upload bumps the design out of Draft into Submitted.
        $this->assertSame(DesignStatus::Submitted, $design->status);

        $revision = $design->revisions()->first();
        $this->assertNotNull($revision);
        $this->assertSame(1, $revision->version);
        $this->assertSame(DesignRevisionStatus::Submitted, $revision->status);
        $this->assertNotNull($revision->file);
    }

    public function test_file_is_required_when_creating_a_design_but_not_when_editing_one(): void
    {
        $project = Project::factory()->create();

        Livewire::actingAs($this->staff)->test(DesignForm::class)
            ->set('project_id', (string) $project->id)
            ->set('title', 'Missing File Design')
            ->call('save')
            ->assertHasErrors(['file' => 'required']);

        $design = Design::factory()->for($project)->create();

        Livewire::actingAs($this->staff)->test(DesignForm::class, ['design' => $design])
            ->set('title', 'Renamed Without New File')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Renamed Without New File', $design->fresh()->title);
    }

    public function test_revisions_are_append_only_and_numbered_sequentially(): void
    {
        $project = Project::factory()->create();
        $design = Design::factory()->for($project)->create(['status' => DesignStatus::Draft->value]);
        $design->revisions()->create([
            'version' => 1,
            'status' => DesignRevisionStatus::Submitted->value,
        ]);

        $secondFile = UploadedFile::fake()->create('revised-plan.pdf', 300, 'application/pdf');

        Livewire::actingAs($this->staff)->test(DesignShow::class, ['design' => $design])
            ->call('toggleRevisionForm')
            ->set('revisionFile', $secondFile)
            ->set('revisionDescription', 'Updated per client notes')
            ->call('addRevision')
            ->assertHasNoErrors();

        $this->assertSame(2, $design->revisions()->count());
        $latest = $design->revisions()->orderByDesc('version')->first();
        $this->assertSame(2, $latest->version);
        $this->assertSame('Updated per client notes', $latest->description);

        // The original first revision row is untouched — append-only.
        $first = $design->revisions()->where('version', 1)->first();
        $this->assertSame(DesignRevisionStatus::Submitted, $first->status);
    }

    public function test_recording_an_approved_client_decision_updates_revision_and_design_status(): void
    {
        $project = Project::factory()->create();
        $design = Design::factory()->for($project)->create(['status' => DesignStatus::UnderReview->value]);
        $revision = $design->revisions()->create([
            'version' => 1,
            'status' => DesignRevisionStatus::ClientReview->value,
        ]);

        Livewire::actingAs($this->staff)->test(DesignShow::class, ['design' => $design])
            ->call('openDecisionForm', $revision->id)
            ->set('decision', ApprovalDecision::Approved->value)
            ->set('decisionComment', 'Looks great, proceed.')
            ->call('recordDecision')
            ->assertHasNoErrors();

        $this->assertSame(DesignRevisionStatus::Approved, $revision->fresh()->status);
        $this->assertSame(DesignStatus::Approved, $design->fresh()->status);
        $this->assertSame(ApprovalDecision::Approved, $design->fresh()->client_approval_status);
        $this->assertSame(1, $revision->approvals()->count());
    }

    public function test_recording_a_revision_requested_decision_leaves_the_design_under_review(): void
    {
        $project = Project::factory()->create();
        $design = Design::factory()->for($project)->create(['status' => DesignStatus::UnderReview->value]);
        $revision = $design->revisions()->create([
            'version' => 1,
            'status' => DesignRevisionStatus::ClientReview->value,
        ]);

        Livewire::actingAs($this->staff)->test(DesignShow::class, ['design' => $design])
            ->call('openDecisionForm', $revision->id)
            ->set('decision', ApprovalDecision::RevisionRequested->value)
            ->set('decisionComment', 'Please adjust the kitchen layout.')
            ->call('recordDecision')
            ->assertHasNoErrors();

        $this->assertSame(DesignRevisionStatus::RevisionRequired, $revision->fresh()->status);
        $this->assertSame(DesignStatus::UnderReview, $design->fresh()->status);
    }

    public function test_uploading_a_revision_requires_the_upload_design_ability_distinct_from_edit(): void
    {
        $editorOnly = User::factory()->create();
        $editorOnly->givePermissionTo(['view_designs', 'edit_designs']);

        $project = Project::factory()->create();
        $design = Design::factory()->for($project)->create();

        Livewire::actingAs($editorOnly)->test(DesignShow::class, ['design' => $design])
            ->call('toggleRevisionForm')
            ->assertForbidden();
    }

    public function test_recording_approval_requires_the_approve_design_ability(): void
    {
        $uploaderOnly = User::factory()->create();
        $uploaderOnly->givePermissionTo(['view_designs', 'upload_design']);

        $project = Project::factory()->create();
        $design = Design::factory()->for($project)->create();
        $revision = $design->revisions()->create([
            'version' => 1,
            'status' => DesignRevisionStatus::ClientReview->value,
        ]);

        Livewire::actingAs($uploaderOnly)->test(DesignShow::class, ['design' => $design])
            ->call('openDecisionForm', $revision->id)
            ->assertForbidden();
    }

    public function test_deleting_a_design_requires_delete_permission(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('view_designs');

        $project = Project::factory()->create();
        $design = Design::factory()->for($project)->create();

        Livewire::actingAs($viewer)->test(DesignIndex::class)
            ->call('deleteDesign', $design->id)
            ->assertForbidden();

        $this->assertNotNull($design->fresh());
    }

    public function test_design_list_can_be_filtered_by_project(): void
    {
        $projectA = Project::factory()->create();
        $projectB = Project::factory()->create();

        Design::factory()->for($projectA)->create(['title' => 'Design For Project A']);
        Design::factory()->for($projectB)->create(['title' => 'Design For Project B']);

        Livewire::actingAs($this->staff)->test(DesignIndex::class)
            ->set('project', (string) $projectA->id)
            ->assertSee('Design For Project A')
            ->assertDontSee('Design For Project B');
    }

    public function test_uploading_a_revision_can_record_a_drawing_number_and_issue_date(): void
    {
        $project = Project::factory()->create();
        $design = Design::factory()->for($project)->create(['status' => DesignStatus::Draft->value]);
        $file = UploadedFile::fake()->create('elevation.pdf', 200, 'application/pdf');

        Livewire::actingAs($this->staff)->test(DesignShow::class, ['design' => $design])
            ->call('toggleRevisionForm')
            ->set('revisionFile', $file)
            ->set('revisionDrawingNo', 'DRG-ELV-03')
            ->set('revisionIssueDate', '2026-02-10')
            ->call('addRevision')
            ->assertHasNoErrors();

        $revision = $design->revisions()->first();
        $this->assertSame('DRG-ELV-03', $revision->drawing_no);
        $this->assertSame('2026-02-10', $revision->issue_date->toDateString());
    }

    public function test_a_revision_uploaded_without_an_issue_date_defaults_to_today(): void
    {
        $project = Project::factory()->create();
        $design = Design::factory()->for($project)->create(['status' => DesignStatus::Draft->value]);
        $file = UploadedFile::fake()->create('elevation.pdf', 200, 'application/pdf');

        Livewire::actingAs($this->staff)->test(DesignShow::class, ['design' => $design])
            ->call('toggleRevisionForm')
            ->set('revisionFile', $file)
            ->call('addRevision')
            ->assertHasNoErrors();

        $revision = $design->revisions()->first();
        $this->assertSame(now()->toDateString(), $revision->issue_date->toDateString());
    }

    public function test_only_the_latest_revision_is_not_marked_superseded(): void
    {
        $project = Project::factory()->create();
        $design = Design::factory()->for($project)->create(['status' => DesignStatus::Draft->value]);

        $v1 = $design->revisions()->create(['version' => 1, 'status' => DesignRevisionStatus::Submitted->value]);
        $v2 = $design->revisions()->create(['version' => 2, 'status' => DesignRevisionStatus::Submitted->value]);

        $this->assertTrue($v1->fresh()->isSuperseded());
        $this->assertFalse($v2->fresh()->isSuperseded());
    }

    public function test_a_single_revision_design_is_not_marked_superseded(): void
    {
        $project = Project::factory()->create();
        $design = Design::factory()->for($project)->create(['status' => DesignStatus::Draft->value]);
        $revision = $design->revisions()->create(['version' => 1, 'status' => DesignRevisionStatus::Submitted->value]);

        $this->assertFalse($revision->fresh()->isSuperseded());
    }

    public function test_the_show_page_marks_an_older_revision_as_superseded(): void
    {
        $project = Project::factory()->create();
        $design = Design::factory()->for($project)->create(['status' => DesignStatus::Draft->value]);
        $design->revisions()->create(['version' => 1, 'drawing_no' => 'DRG-01', 'status' => DesignRevisionStatus::Submitted->value]);
        $design->revisions()->create(['version' => 2, 'drawing_no' => 'DRG-02', 'status' => DesignRevisionStatus::Submitted->value]);

        Livewire::actingAs($this->staff)->test(DesignShow::class, ['design' => $design])
            ->assertSee('Superseded — do not use on site')
            ->assertSee('DRG-01')
            ->assertSee('DRG-02');
    }
}
