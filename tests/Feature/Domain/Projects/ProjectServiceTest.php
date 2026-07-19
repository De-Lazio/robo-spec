<?php

namespace Tests\Feature\Domain\Projects;

use App\Domain\Projects\DTOs\CreateProjectData;
use App\Domain\Projects\DTOs\UpdateProjectData;
use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Domain\Projects\Enums\ProjectStatus;
use App\Domain\Projects\Enums\RobotType;
use App\Domain\Projects\Services\ProjectService;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Notifications\ProjectActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_notifies_other_members_when_the_project_is_updated(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $manager = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        ProjectMember::query()->create([
            'project_id' => $project->getKey(),
            'user_id' => $manager->getKey(),
            'role' => ProjectMemberRole::Manager,
            'joined_at' => now(),
        ]);

        app(ProjectService::class)->update($project, $owner, new UpdateProjectData(
            name: $project->name,
            description: null,
            robotType: $project->robot_type,
            domain: null,
            status: $project->status,
        ));

        Notification::assertSentTo($manager, ProjectActivityNotification::class);
        Notification::assertNotSentTo($owner, ProjectActivityNotification::class);
    }

    public function test_it_creates_a_project_with_its_owner_tags_and_activity(): void
    {
        $owner = User::factory()->create();

        $project = app(ProjectService::class)->create($owner, new CreateProjectData(
            name: '  Robot Transporteur AGV  ',
            description: '  Robot autonome de transport.  ',
            robotType: RobotType::Mobile,
            domain: '  Industrie  ',
            tags: ['ESP32', ' LIDAR ', 'esp32', '', 'Moteurs DC'],
        ));

        $this->assertTrue($project->exists);
        $this->assertSame('Robot Transporteur AGV', $project->name);
        $this->assertSame('robot-transporteur-agv', $project->slug);
        $this->assertSame(ProjectStatus::Draft, $project->status);
        $this->assertSame(0, $project->progress);

        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->getKey(),
            'user_id' => $owner->getKey(),
            'role' => ProjectMemberRole::Owner->value,
        ]);
        $this->assertDatabaseCount('project_tags', 3);
        $this->assertDatabaseHas('project_activities', [
            'project_id' => $project->getKey(),
            'actor_id' => $owner->getKey(),
            'event' => 'project.created',
        ]);
    }

    public function test_it_generates_a_unique_slug_when_a_project_name_already_exists(): void
    {
        $owner = User::factory()->create();
        Project::factory()->create(['name' => 'Robot AGV', 'slug' => 'robot-agv']);

        $project = app(ProjectService::class)->create($owner, new CreateProjectData(
            name: 'Robot AGV',
            description: null,
            robotType: RobotType::Mobile,
            domain: null,
        ));

        $this->assertSame('robot-agv-2', $project->slug);
    }

    public function test_progress_follows_status_transitions_and_freezes_when_archived(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey(), 'status' => ProjectStatus::Draft, 'progress' => 0]);
        $service = app(ProjectService::class);

        $updateWithStatus = fn (ProjectStatus $status): UpdateProjectData => new UpdateProjectData(
            name: $project->name,
            description: null,
            robotType: $project->robot_type,
            domain: null,
            status: $status,
        );

        $project = $service->update($project, $owner, $updateWithStatus(ProjectStatus::Testing));
        $this->assertSame(75, $project->progress);

        $service->archive($project, $owner);
        $this->assertSame(75, $project->fresh()->progress);

        $service->restore($project, $owner);
        $this->assertSame(0, $project->fresh()->progress);

        $project = $service->update($project->fresh(), $owner, $updateWithStatus(ProjectStatus::Completed));
        $this->assertSame(100, $project->progress);
    }
}
