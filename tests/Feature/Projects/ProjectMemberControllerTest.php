<?php

namespace Tests\Feature\Projects;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Notifications\ProjectInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectMemberControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_outsider_cannot_access_or_manage_members(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $member = $this->createMember($project, User::factory()->create(), ProjectMemberRole::Contributor);
        $outsider = User::factory()->create();

        $this->actingAs($outsider)->get(route('projects.members.index', $project))->assertForbidden();
        $this->actingAs($outsider)->post(route('projects.members.invitations.store', $project), [
            'email' => 'someone@example.com',
            'role' => 'contributor',
        ])->assertForbidden();
        $this->actingAs($outsider)->put(route('projects.members.update', [$project, $member]), ['role' => 'manager'])->assertForbidden();
        $this->actingAs($outsider)->delete(route('projects.members.destroy', [$project, $member]))->assertForbidden();
    }

    public function test_an_owner_can_invite_and_change_a_members_role(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $member = $this->createMember($project, User::factory()->create(), ProjectMemberRole::Contributor);

        $this->actingAs($owner)->post(route('projects.members.invitations.store', $project), [
            'email' => 'invitee@example.com',
            'role' => 'viewer',
        ])->assertRedirect();

        $this->assertDatabaseHas('project_invitations', ['project_id' => $project->getKey(), 'email' => 'invitee@example.com']);

        $this->actingAs($owner)
            ->get(route('projects.members.index', $project))
            ->assertInertia(fn (Assert $page) => $page->component('Projects/Members')->has('invitations', 1));

        $this->actingAs($owner)->put(route('projects.members.update', [$project, $member]), ['role' => 'manager'])->assertRedirect();
        $this->assertDatabaseHas('project_members', ['id' => $member->getKey(), 'role' => ProjectMemberRole::Manager->value]);
    }

    public function test_a_manager_cannot_touch_the_owners_membership(): void
    {
        $owner = User::factory()->create();
        $manager = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $ownerMember = $this->createMember($project, $owner, ProjectMemberRole::Owner);
        $this->createMember($project, $manager, ProjectMemberRole::Manager);

        $this->actingAs($manager)->put(route('projects.members.update', [$project, $ownerMember]), ['role' => 'contributor'])->assertForbidden();
        $this->actingAs($manager)->delete(route('projects.members.destroy', [$project, $ownerMember]))->assertForbidden();
    }

    public function test_the_owner_cannot_remove_themself(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $ownerMember = $this->createMember($project, $owner, ProjectMemberRole::Owner);

        $this->actingAs($owner)
            ->delete(route('projects.members.destroy', [$project, $ownerMember]))
            ->assertForbidden();
    }

    public function test_a_full_guest_can_register_and_join_via_an_invitation_link(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);

        $this->actingAs($owner)->post(route('projects.members.invitations.store', $project), [
            'email' => 'newcomer@example.com',
            'role' => 'contributor',
        ])->assertRedirect();

        $capturedToken = null;
        Notification::assertSentOnDemand(
            ProjectInvitationNotification::class,
            function (ProjectInvitationNotification $notification) use (&$capturedToken): bool {
                $capturedToken = $notification->plainToken;

                return true;
            },
        );
        $this->assertNotNull($capturedToken);

        $acceptUrl = route('invitations.accept', ['token' => $capturedToken]);

        $this->post(route('logout'));

        $this->get($acceptUrl)->assertInertia(fn (Assert $page) => $page->component('Invitations/Accept')->where('status', 'guest'));

        $this->post(route('register'), [
            'name' => 'Newcomer',
            'email' => 'newcomer@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect($acceptUrl);

        $newcomer = User::query()->where('email', 'newcomer@example.com')->firstOrFail();

        $this->get($acceptUrl)->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->getKey(),
            'user_id' => $newcomer->getKey(),
            'role' => ProjectMemberRole::Contributor->value,
        ]);
    }

    public function test_email_mismatch_does_not_create_a_membership(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $otherUser = User::factory()->create();

        $this->actingAs($owner)->post(route('projects.members.invitations.store', $project), [
            'email' => 'invitee@example.com',
            'role' => 'viewer',
        ])->assertRedirect();

        $capturedToken = null;
        Notification::assertSentOnDemand(
            ProjectInvitationNotification::class,
            function (ProjectInvitationNotification $notification) use (&$capturedToken): bool {
                $capturedToken = $notification->plainToken;

                return true;
            },
        );

        $this->actingAs($otherUser)
            ->get(route('invitations.accept', ['token' => $capturedToken]))
            ->assertInertia(fn (Assert $page) => $page->component('Invitations/Accept')->where('status', 'email_mismatch'));

        $this->assertDatabaseMissing('project_members', ['project_id' => $project->getKey(), 'user_id' => $otherUser->getKey()]);
    }

    private function createMember(Project $project, User $user, ProjectMemberRole $role): ProjectMember
    {
        return ProjectMember::query()->create([
            'project_id' => $project->getKey(),
            'user_id' => $user->getKey(),
            'role' => $role,
            'joined_at' => now(),
        ]);
    }
}
