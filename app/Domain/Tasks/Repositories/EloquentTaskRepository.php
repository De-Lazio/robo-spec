<?php

namespace App\Domain\Tasks\Repositories;

use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Task
    {
        return Task::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Task $task, array $attributes): Task
    {
        $task->update($attributes);

        return $task;
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }

    /**
     * @return Collection<int, Task>
     */
    public function forProject(string $projectId): Collection
    {
        return Task::query()
            ->where('project_id', $projectId)
            ->with('assignee')
            ->orderBy('created_at')
            ->get();
    }
}
