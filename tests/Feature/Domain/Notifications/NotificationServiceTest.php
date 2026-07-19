<?php

namespace Tests\Feature\Domain\Notifications;

use App\Domain\Notifications\Services\NotificationService;
use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectMember;
use App\Models\User;
use App\Notifications\ProjectActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_notifies_every_member_except_the_actor(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $contributor = User::factory()->create();
        $viewer = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $this->addMember($project, $contributor, ProjectMemberRole::Contributor);
        $this->addMember($project, $viewer, ProjectMemberRole::Viewer);

        $activity = ProjectActivity::query()->create([
            'project_id' => $project->getKey(),
            'actor_id' => $contributor->getKey(),
            'event' => 'resource.uploaded',
            'subject_type' => null,
            'subject_id' => null,
            'properties' => ['name' => 'plan.pdf'],
        ]);

        app(NotificationService::class)->notifyProjectEvent($activity);

        Notification::assertSentTo($owner, ProjectActivityNotification::class);
        Notification::assertSentTo($viewer, ProjectActivityNotification::class);
        Notification::assertNotSentTo($contributor, ProjectActivityNotification::class);
    }

    public function test_owner_is_never_duplicated_when_also_a_member_row(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $this->addMember($project, $owner, ProjectMemberRole::Owner);
        $other = User::factory()->create();

        $activity = ProjectActivity::query()->create([
            'project_id' => $project->getKey(),
            'actor_id' => $other->getKey(),
            'event' => 'project.updated',
            'subject_type' => null,
            'subject_id' => null,
            'properties' => [],
        ]);

        app(NotificationService::class)->notifyProjectEvent($activity);

        Notification::assertSentToTimes($owner, ProjectActivityNotification::class, 1);
    }

    public function test_only_requirements_published_triggers_the_mail_channel(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $actor = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $this->addMember($project, $actor, ProjectMemberRole::Contributor);

        $published = ProjectActivity::query()->create([
            'project_id' => $project->getKey(),
            'actor_id' => $actor->getKey(),
            'event' => 'requirements.published',
            'subject_type' => null,
            'subject_id' => null,
            'properties' => ['version' => 1],
        ]);

        $uploaded = ProjectActivity::query()->create([
            'project_id' => $project->getKey(),
            'actor_id' => $actor->getKey(),
            'event' => 'resource.uploaded',
            'subject_type' => null,
            'subject_id' => null,
            'properties' => ['name' => 'plan.pdf'],
        ]);

        app(NotificationService::class)->notifyProjectEvent($published);
        app(NotificationService::class)->notifyProjectEvent($uploaded);

        Notification::assertSentTo($owner, ProjectActivityNotification::class, function (ProjectActivityNotification $notification, array $channels): bool {
            return $notification->activity->event === 'requirements.published' && in_array('mail', $channels, true);
        });

        Notification::assertSentTo($owner, ProjectActivityNotification::class, function (ProjectActivityNotification $notification, array $channels): bool {
            return $notification->activity->event === 'resource.uploaded' && ! in_array('mail', $channels, true);
        });
    }

    private function addMember(Project $project, User $user, ProjectMemberRole $role): ProjectMember
    {
        return ProjectMember::query()->create([
            'project_id' => $project->getKey(),
            'user_id' => $user->getKey(),
            'role' => $role,
            'joined_at' => now(),
        ]);
    }
}
