<?php

namespace Tests\Feature;

use App\Livewire\Meetings\Form as MeetingForm;
use App\Livewire\Meetings\Index as MeetingIndex;
use App\Models\Client;
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\Project;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MeetingTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo(['view_meetings', 'create_meetings', 'edit_meetings', 'delete_meetings']);
    }

    public function test_a_user_without_view_meetings_permission_cannot_see_the_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(MeetingIndex::class)
            ->assertForbidden();
    }

    public function test_creating_a_meeting_with_participants_resolves_each_polymorphic_type(): void
    {
        $project = Project::factory()->create();
        $otherUser = User::factory()->create();
        $client = Client::factory()->create();
        $vendor = Vendor::factory()->create();

        Livewire::actingAs($this->staff)->test(MeetingForm::class)
            ->set('project_id', (string) $project->id)
            ->set('type', 'client')
            ->set('date', now()->toDateString())
            ->set('participant_users', [(string) $otherUser->id])
            ->set('participant_clients', [(string) $client->id])
            ->set('participant_vendors', [(string) $vendor->id])
            ->call('save')
            ->assertHasNoErrors();

        $meeting = Meeting::first();
        $this->assertNotNull($meeting);
        $this->assertSame(3, $meeting->participants()->count());

        $userRow = MeetingParticipant::where('participant_type', User::class)->where('participant_id', $otherUser->id)->first();
        $clientRow = MeetingParticipant::where('participant_type', Client::class)->where('participant_id', $client->id)->first();
        $vendorRow = MeetingParticipant::where('participant_type', Vendor::class)->where('participant_id', $vendor->id)->first();

        $this->assertNotNull($userRow);
        $this->assertNotNull($clientRow);
        $this->assertNotNull($vendorRow);

        $this->assertSame($otherUser->name, $userRow->participantName());
        $this->assertSame($client->name, $clientRow->participantName());
        $this->assertSame($vendor->company_name, $vendorRow->participantName());
    }
}
