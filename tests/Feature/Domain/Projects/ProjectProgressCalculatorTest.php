<?php

namespace Tests\Feature\Domain\Projects;

use App\Domain\Projects\Enums\ProjectStatus;
use App\Domain\Projects\Services\ProjectProgressCalculator;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectProgressCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_maps_each_status_to_a_deterministic_progress_when_there_are_no_tasks(): void
    {
        $calculator = app(ProjectProgressCalculator::class);
        $project = Project::factory()->create();

        $this->assertSame(0, $calculator->calculate($project, ProjectStatus::Draft, 60));
        $this->assertSame(40, $calculator->calculate($project, ProjectStatus::InProgress, 0));
        $this->assertSame(75, $calculator->calculate($project, ProjectStatus::Testing, 0));
        $this->assertSame(100, $calculator->calculate($project, ProjectStatus::Completed, 0));
    }

    public function test_archived_projects_without_tasks_keep_their_current_progress(): void
    {
        $calculator = app(ProjectProgressCalculator::class);
        $project = Project::factory()->create();

        $this->assertSame(75, $calculator->calculate($project, ProjectStatus::Archived, 75));
        $this->assertSame(0, $calculator->calculate($project, ProjectStatus::Archived, 0));
    }

    public function test_progress_reflects_the_ratio_of_done_tasks_once_tasks_exist(): void
    {
        $calculator = app(ProjectProgressCalculator::class);
        $project = Project::factory()->create();

        Task::factory()->for($project)->done()->create();
        Task::factory()->for($project)->create();
        Task::factory()->for($project)->create();
        Task::factory()->for($project)->create();

        $this->assertSame(25, $calculator->calculate($project, ProjectStatus::InProgress, 0));
    }

    public function test_tasks_win_over_the_frozen_archived_progress(): void
    {
        $calculator = app(ProjectProgressCalculator::class);
        $project = Project::factory()->create();

        Task::factory()->for($project)->done()->create();
        Task::factory()->for($project)->create();

        $this->assertSame(50, $calculator->calculate($project, ProjectStatus::Archived, 75));
    }
}
