<?php

namespace App\Domain\Tasks\Services;

use App\Domain\Projects\Contracts\ProjectActivityRepositoryInterface;
use App\Domain\Projects\Services\ProjectProgressCalculator;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Domain\Tasks\DTOs\TaskData;
use App\Domain\Tasks\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function __construct(
        private readonly TaskRepositoryInterface $tasks,
        private readonly ProjectActivityRepositoryInterface $activities,
        private readonly ProjectProgressCalculator $progress,
    ) {}

    public function create(Project $project, User $actor, TaskData $data): Task
    {
        return DB::transaction(function () use ($project, $actor, $data): Task {
            $task = $this->tasks->create([
                'project_id' => $project->getKey(),
                'title' => $data->title,
                'description' => $data->description,
                'status' => TaskStatus::Todo,
                'assignee_id' => $data->assigneeId,
                'due_date' => $data->dueDate,
                'linked_function_ids' => $data->linkedFunctionIds,
                'created_by' => $actor->getKey(),
            ]);

            $this->activities->create([
                'project_id' => $project->getKey(),
                'actor_id' => $actor->getKey(),
                'event' => 'task.created',
                'subject_type' => Task::class,
                'subject_id' => $task->getKey(),
                'properties' => ['title' => $task->title],
            ]);

            $this->recalculateProgress($project);

            return $task;
        });
    }

    public function update(Task $task, TaskData $data): Task
    {
        return $this->tasks->update($task, [
            'title' => $data->title,
            'description' => $data->description,
            'assignee_id' => $data->assigneeId,
            'due_date' => $data->dueDate,
            'linked_function_ids' => $data->linkedFunctionIds,
        ]);
    }

    public function updateStatus(Project $project, Task $task, User $actor, TaskStatus $status): Task
    {
        return DB::transaction(function () use ($project, $task, $actor, $status): Task {
            $task = $this->tasks->update($task, ['status' => $status]);

            $this->activities->create([
                'project_id' => $project->getKey(),
                'actor_id' => $actor->getKey(),
                'event' => 'task.status_changed',
                'subject_type' => Task::class,
                'subject_id' => $task->getKey(),
                'properties' => ['title' => $task->title, 'status' => $status->value],
            ]);

            $this->recalculateProgress($project);

            return $task;
        });
    }

    public function delete(Project $project, Task $task, User $actor): void
    {
        DB::transaction(function () use ($project, $task, $actor): void {
            $this->activities->create([
                'project_id' => $project->getKey(),
                'actor_id' => $actor->getKey(),
                'event' => 'task.deleted',
                'subject_type' => Task::class,
                'subject_id' => $task->getKey(),
                'properties' => ['title' => $task->title],
            ]);

            $this->tasks->delete($task);
            $this->recalculateProgress($project);
        });
    }

    private function recalculateProgress(Project $project): void
    {
        $project->update([
            'progress' => $this->progress->calculate($project, $project->status, $project->progress),
        ]);
    }
}
