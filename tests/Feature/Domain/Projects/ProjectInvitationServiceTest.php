<?php

namespace Tests\Feature\Domain\Projects;

use App\Domain\Projects\DTOs\InviteMemberData;
use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Domain\Projects\Services\ProjectInvitationService;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Notifications\ProjectInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProjectInvitationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_invitation_and_notifies_the_invitee(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);

        $invitation = app(ProjectInvitationService::class)->invite($project, $owner, new InviteMemberData(
            email: 'Invitee@Example.com',
            role: ProjectMemberRole::Contributor,
        ));

        $this->assertSame('invitee@example.com', $invitation->email);
        $this->assertNotEmpty($invitation->token);
        $this->assertDatabaseHas('project_activities', [
            'project_id' => $project->getKey(),
            'event' => 'member.invited',
        ]);

        Notification::assertSentOnDemand(
            ProjectInvitationNotification::class,
            fn (ProjectInvitationNotification $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notifiable->routes['mail'] === 'invitee@example.com',
        );
    }

    public function test_it_rejects_a_duplicate_pending_invitation(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(ProjectInvitationService::class);

        $service->invite($project, $owner, new InviteMemberData('invitee@example.com', ProjectMemberRole::Viewer));

        $this->expectException(ValidationException::class);
        $service->invite($project, $owner, new InviteMemberData('invitee@example.com', ProjectMemberRole::Manager));
    }

    public function test_it_reuses_the_invitation_row_after_expiry(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(ProjectInvitationService::class);

        $first = $service->invite($project, $owner, new InviteMemberData('invitee@example.com', ProjectMemberRole::Viewer));
        $first->forceFill(['expires_at' => now()->subDay()])->save();

        $second = $service->invite($project, $owner, new InviteMemberData('invitee@example.com', ProjectMemberRole::Manager));

        $this->assertSame($first->getKey(), $second->getKey());
        $this->assertSame(ProjectMemberRole::Manager, $second->role);
        $this->assertDatabaseCount('project_invitations', 1);
    }

    public function test_it_rejects_inviting_an_existing_member(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $member = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        ProjectMember::query()->create([
            'project_id' => $project->getKey(),
            'user_id' => $member->getKey(),
            'role' => ProjectMemberRole::Contributor,
            'joined_at' => now(),
        ]);

        $this->expectException(ValidationException::class);
        app(ProjectInvitationService::class)->invite($project, $owner, new InviteMemberData($member->email, ProjectMemberRole::Viewer));
    }

    public function test_it_accepts_an_invitation_and_creates_a_membership(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $invitee = User::factory()->create(['email' => 'invitee@example.com']);
        $service = app(ProjectInvitationService::class);

        $invitation = $service->invite($project, $owner, new InviteMemberData('invitee@example.com', ProjectMemberRole::Manager));

        $member = $service->accept($invitation, $invitee);

        $this->assertSame(ProjectMemberRole::Manager, $member->role);
        $this->assertTrue($invitation->fresh()->isAccepted());
        $this->assertDatabaseHas('project_activities', ['event' => 'member.joined']);
    }
}
