<?php

namespace Tests\Feature\Domain\Tasks;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Domain\Tasks\DTOs\TaskData;
use App\Domain\Tasks\Enums\TaskStatus;
use App\Domain\Tasks\Services\TaskService;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Notifications\ProjectActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_notifies_other_members(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $contributor = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        ProjectMember::query()->create([
            'project_id' => $project->getKey(),
            'user_id' => $contributor->getKey(),
            'role' => ProjectMemberRole::Contributor,
            'joined_at' => now(),
        ]);

        app(TaskService::class)->create($project, $owner, new TaskData('Tâche 1', null, null, null));

        Notification::assertSentTo($contributor, ProjectActivityNotification::class);
        Notification::assertNotSentTo($owner, ProjectActivityNotification::class);
    }

    public function test_create_stores_the_task_as_todo_and_logs_an_activity(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(TaskService::class);

        $task = $service->create($project, $owner, new TaskData(
            title: 'Concevoir le châssis',
            description: 'CAO complète',
            assigneeId: null,
            dueDate: '2026-08-01',
            linkedFunctionIds: ['F1'],
        ));

        $this->assertSame(TaskStatus::Todo, $task->status);
        $this->assertSame(['F1'], $task->linked_function_ids);
        $this->assertDatabaseHas('tasks', ['project_id' => $project->getKey(), 'title' => 'Concevoir le châssis']);
        $this->assertDatabaseHas('project_activities', ['project_id' => $project->getKey(), 'event' => 'task.created']);
    }

    public function test_create_recalculates_project_progress(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(TaskService::class);

        $service->create($project, $owner, new TaskData('Tâche 1', null, null, null));

        $this->assertSame(0, $project->fresh()->progress);
    }

    public function test_update_status_moves_the_task_and_recalculates_progress(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(TaskService::class);

        $task = $service->create($project, $owner, new TaskData('Tâche 1', null, null, null));
        $service->create($project, $owner, new TaskData('Tâche 2', null, null, null));

        $updated = $service->updateStatus($project, $task, $owner, TaskStatus::Done);

        $this->assertSame(TaskStatus::Done, $updated->status);
        $this->assertSame(50, $project->fresh()->progress);
        $this->assertDatabaseHas('project_activities', ['project_id' => $project->getKey(), 'event' => 'task.status_changed']);
    }

    public function test_update_edits_fields_without_touching_status(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(TaskService::class);

        $task = $service->create($project, $owner, new TaskData('Tâche 1', null, null, null));

        $updated = $service->update($task, new TaskData('Tâche renommée', 'Nouvelle description', null, '2026-09-01', ['F2']));

        $this->assertSame('Tâche renommée', $updated->title);
        $this->assertSame('Nouvelle description', $updated->description);
        $this->assertSame(['F2'], $updated->linked_function_ids);
        $this->assertSame(TaskStatus::Todo, $updated->status);
    }

    public function test_delete_removes_the_task_logs_an_activity_and_recalculates_progress(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(TaskService::class);

        $task = $service->create($project, $owner, new TaskData('Tâche 1', null, null, null));
        $service->create($project, $owner, new TaskData('Tâche 2', null, null, null));
        $service->updateStatus($project, $task, $owner, TaskStatus::Done);

        $service->delete($project, $task, $owner);

        $this->assertDatabaseMissing('tasks', ['id' => $task->getKey()]);
        $this->assertDatabaseHas('project_activities', ['project_id' => $project->getKey(), 'event' => 'task.deleted']);
        $this->assertSame(0, $project->fresh()->progress);
    }
}
