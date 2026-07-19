<?php

namespace App\Domain\Tasks\Contracts;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Task;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Task $task, array $attributes): Task;

    public function delete(Task $task): void;

    /**
     * @return Collection<int, Task>
     */
    public function forProject(string $projectId): Collection;
}
