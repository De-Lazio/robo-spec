<?php

namespace Tests\Feature\Domain\Projects;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Domain\Projects\Services\ProjectMembershipService;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Notifications\ProjectActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProjectMembershipServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_a_members_role_and_logs_activity(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $member = $this->createMember($project, User::factory()->create(), ProjectMemberRole::Contributor);

        $updated = app(ProjectMembershipService::class)->updateRole($project, $member, $owner, ProjectMemberRole::Manager);

        $this->assertSame(ProjectMemberRole::Manager, $updated->role);
        $this->assertDatabaseHas('project_activities', [
            'project_id' => $project->getKey(),
            'event' => 'member.role_updated',
        ]);
    }

    public function test_it_notifies_the_member_whose_role_changed(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $memberUser = User::factory()->create();
        $member = $this->createMember($project, $memberUser, ProjectMemberRole::Contributor);

        app(ProjectMembershipService::class)->updateRole($project, $member, $owner, ProjectMemberRole::Manager);

        Notification::assertSentTo($memberUser, ProjectActivityNotification::class);
        Notification::assertNotSentTo($owner, ProjectActivityNotification::class);
    }

    public function test_it_removes_a_member_and_logs_activity(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $member = $this->createMember($project, User::factory()->create(), ProjectMemberRole::Viewer);

        app(ProjectMembershipService::class)->remove($project, $member, $owner);

        $this->assertDatabaseMissing('project_members', ['id' => $member->getKey()]);
        $this->assertDatabaseHas('project_activities', [
            'project_id' => $project->getKey(),
            'event' => 'member.removed',
        ]);
    }

    public function test_it_prevents_the_owner_being_demoted(): void
    {
        $owner = User::factory()->create();
        $manager = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $ownerMember = $this->createMember($project, $owner, ProjectMemberRole::Owner);
        $this->createMember($project, $manager, ProjectMemberRole::Manager);

        $this->expectException(ValidationException::class);
        app(ProjectMembershipService::class)->updateRole($project, $ownerMember, $manager, ProjectMemberRole::Contributor);
    }

    public function test_it_prevents_the_owner_being_removed_even_by_themself(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $ownerMember = $this->createMember($project, $owner, ProjectMemberRole::Owner);

        $this->expectException(ValidationException::class);
        app(ProjectMembershipService::class)->remove($project, $ownerMember, $owner);
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
