<?php

namespace Tests\Feature\Domain\Projects;

use App\Domain\Projects\DTOs\CreateProjectData;
use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Domain\Projects\Enums\ProjectStatus;
use App\Domain\Projects\Enums\RobotType;
use App\Domain\Projects\Services\ProjectService;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectServiceTest extends TestCase
{
    use RefreshDatabase;

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
}
