<?php

namespace Tests\Feature\Notifications;

use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\User;
use App\Notifications\ProjectActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_the_authenticated_users_notifications(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->getKey()]);
        $activity = $this->makeActivity($project, $user);

        $user->notify(new ProjectActivityNotification($activity));
        $other->notify(new ProjectActivityNotification($activity));

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Notifications/Index')
            ->has('notifications.data', 1));
    }

    public function test_a_user_can_mark_their_own_notification_as_read(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->getKey()]);
        $activity = $this->makeActivity($project, $user);

        $user->notify(new ProjectActivityNotification($activity));
        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($user)->post(route('notifications.read', $notification->id))->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_a_user_cannot_mark_another_users_notification_as_read(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->getKey()]);
        $activity = $this->makeActivity($project, $user);

        $other->notify(new ProjectActivityNotification($activity));
        $notification = $other->notifications()->firstOrFail();

        $this->actingAs($user)->post(route('notifications.read', $notification->id))->assertRedirect();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_mark_all_as_read_only_touches_the_authenticated_users_notifications(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->getKey()]);
        $activity = $this->makeActivity($project, $user);

        $user->notify(new ProjectActivityNotification($activity));
        $other->notify(new ProjectActivityNotification($activity));

        $this->actingAs($user)->post(route('notifications.read-all'))->assertRedirect();

        $this->assertNotNull($user->notifications()->firstOrFail()->fresh()->read_at);
        $this->assertNull($other->notifications()->firstOrFail()->fresh()->read_at);
    }

    private function makeActivity(Project $project, User $actor): ProjectActivity
    {
        return ProjectActivity::query()->create([
            'project_id' => $project->getKey(),
            'actor_id' => $actor->getKey(),
            'event' => 'project.updated',
            'subject_type' => null,
            'subject_id' => null,
            'properties' => ['name' => $project->name],
        ]);
    }
}
