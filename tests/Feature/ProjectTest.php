<?php

namespace Tests\Feature;

use App\Enums\MilestoneStatus;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Livewire\Projects\Form as ProjectForm;
use App\Livewire\Projects\Index as ProjectIndex;
use App\Livewire\Projects\Show as ProjectShow;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo(['view_projects', 'create_projects', 'edit_projects', 'delete_projects']);
    }

    public function test_a_user_without_view_permission_cannot_see_the_project_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(ProjectIndex::class)
            ->assertForbidden();
    }

    public function test_creating_a_project_assigns_a_sequential_project_code(): void
    {
        $client = Client::factory()->create();

        Livewire::actingAs($this->staff)->test(ProjectForm::class)
            ->set('client_id', (string) $client->id)
            ->set('name', 'Riverside Apartment Interior')
            ->call('save')
            ->assertHasNoErrors();

        $project = Project::first();
        $this->assertNotNull($project);
        $this->assertStringStartsWith('PRJ-', $project->project_code);
        $this->assertSame(ProjectStatus::Planning, $project->status);
    }

    public function test_name_and_client_are_required_to_create_a_project(): void
    {
        Livewire::actingAs($this->staff)->test(ProjectForm::class)
            ->set('name', '')
            ->set('client_id', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required', 'client_id' => 'required']);
    }

    public function test_creating_a_project_from_a_lead_stamps_the_leads_converted_project_id(): void
    {
        $client = Client::factory()->create();
        $lead = Lead::factory()->create(['converted_client_id' => $client->id]);

        Livewire::actingAs($this->staff)->test(ProjectForm::class)
            ->set('client_id', (string) $client->id)
            ->set('name', 'Lead-originated Project')
            ->set('lead_id', $lead->id) // simulates the ?lead= query-string prefill normally set in mount()
            ->call('save')
            ->assertHasNoErrors();

        $project = Project::first();
        $this->assertSame($project->id, $lead->fresh()->converted_project_id);
        $this->assertTrue($lead->fresh()->hasProject());
    }

    public function test_adding_a_milestone_updates_project_progress_to_its_average(): void
    {
        $project = Project::factory()->create();

        $component = Livewire::actingAs($this->staff)->test(ProjectShow::class, ['project' => $project])
            ->set('milestone_name', 'Design Approval')
            ->set('milestone_priority', 'high')
            ->call('addMilestone')
            ->assertHasNoErrors();

        $milestone = $project->milestones()->first();
        $this->assertNotNull($milestone);
        $this->assertSame(MilestoneStatus::Pending, $milestone->status);

        $component->call('updateMilestoneStatus', $milestone->id, MilestoneStatus::Completed->value);

        $this->assertSame(100, $project->fresh()->progress);
    }

    public function test_adding_a_task_assigns_a_sequential_task_number(): void
    {
        $project = Project::factory()->create();

        Livewire::actingAs($this->staff)->test(ProjectShow::class, ['project' => $project])
            ->set('task_name', 'Order curtain fabric')
            ->set('task_priority', 'medium')
            ->call('addTask')
            ->assertHasNoErrors();

        $task = $project->tasks()->first();
        $this->assertNotNull($task);
        $this->assertStringStartsWith('TSK-', $task->task_no);
        $this->assertSame(TaskStatus::Todo, $task->status);
    }

    public function test_adding_and_removing_a_team_member(): void
    {
        $project = Project::factory()->create();
        $member = User::factory()->create();

        $component = Livewire::actingAs($this->staff)->test(ProjectShow::class, ['project' => $project])
            ->set('member_user_id', (string) $member->id)
            ->set('member_role', 'Site carpenter lead')
            ->call('addMember')
            ->assertHasNoErrors();

        $this->assertTrue($project->fresh()->members->contains($member));

        $component->call('removeMember', $member->id);

        $this->assertFalse($project->fresh()->members->contains($member));
    }

    public function test_sub_actions_require_the_edit_projects_permission(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('view_projects');

        $project = Project::factory()->create();

        Livewire::actingAs($viewer)->test(ProjectShow::class, ['project' => $project])
            ->set('milestone_name', 'Unauthorized Milestone')
            ->call('addMilestone')
            ->assertForbidden();
    }

    public function test_deleting_a_project_requires_delete_permission(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('view_projects');

        $project = Project::factory()->create();

        Livewire::actingAs($viewer)->test(ProjectIndex::class)
            ->call('deleteProject', $project->id)
            ->assertForbidden();

        $this->assertNotNull($project->fresh());
    }

    public function test_project_list_can_be_filtered_by_status(): void
    {
        Project::factory()->create(['name' => 'On Hold Project', 'status' => ProjectStatus::OnHold->value]);
        Project::factory()->create(['name' => 'Planning Project', 'status' => ProjectStatus::Planning->value]);

        Livewire::actingAs($this->staff)->test(ProjectIndex::class)
            ->set('status', ProjectStatus::OnHold->value)
            ->assertSee('On Hold Project')
            ->assertDontSee('Planning Project');
    }
}
