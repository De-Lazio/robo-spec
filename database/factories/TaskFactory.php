<?php

namespace Database\Factories;

use App\Domain\Tasks\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'status' => TaskStatus::Todo,
            'assignee_id' => null,
            'due_date' => null,
            'linked_function_ids' => [],
            'created_by' => User::factory(),
        ];
    }

    public function done(): static
    {
        return $this->state(fn (): array => ['status' => TaskStatus::Done]);
    }
}
