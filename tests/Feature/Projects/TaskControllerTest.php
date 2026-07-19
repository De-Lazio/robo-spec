<?php

namespace Tests\Feature\Projects;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Domain\Tasks\Enums\TaskStatus;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_outsider_cannot_view_or_manage_tasks(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $outsider = User::factory()->create();

        $this->actingAs($outsider)->get(route('projects.tasks.index', $project))->assertForbidden();
        $this->actingAs($outsider)->post(route('projects.tasks.store', $project), ['title' => 'Tâche'])->assertForbidden();
    }

    public function test_a_contributor_can_create_update_and_delete_a_task(): void
    {
        $owner = User::factory()->create();
        $contributor = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $this->createMember($project, $contributor, ProjectMemberRole::Contributor);

        $this->actingAs($contributor)->post(route('projects.tasks.store', $project), [
            'title' => 'Assembler le châssis',
            'description' => 'Montage complet',
        ])->assertRedirect();

        $task = Task::query()->where('project_id', $project->getKey())->firstOrFail();
        $this->assertSame('todo', $task->status->value);

        $this->actingAs($contributor)->put(route('projects.tasks.update', [$project, $task]), [
            'title' => 'Assembler le châssis (v2)',
        ])->assertRedirect();
        $this->assertDatabaseHas('tasks', ['id' => $task->getKey(), 'title' => 'Assembler le châssis (v2)']);

        $this->actingAs($contributor)
            ->delete(route('projects.tasks.destroy', [$project, $task]))
            ->assertRedirect();
        $this->assertDatabaseMissing('tasks', ['id' => $task->getKey()]);
    }

    public function test_assigning_a_task_to_a_non_member_is_rejected(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $outsider = User::factory()->create();

        $this->actingAs($owner)->post(route('projects.tasks.store', $project), [
            'title' => 'Tâche',
            'assignee_id' => $outsider->getKey(),
        ])->assertSessionHasErrors('assignee_id');
    }

    public function test_moving_a_task_to_done_updates_the_projects_progress(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);

        $this->actingAs($owner)->post(route('projects.tasks.store', $project), ['title' => 'Tâche 1'])->assertRedirect();
        $this->actingAs($owner)->post(route('projects.tasks.store', $project), ['title' => 'Tâche 2'])->assertRedirect();
        $task = Task::query()->where('project_id', $project->getKey())->firstOrFail();

        $this->actingAs($owner)
            ->patch(route('projects.tasks.status.update', [$project, $task]), ['status' => TaskStatus::Done->value])
            ->assertRedirect();

        $this->assertSame(50, $project->fresh()->progress);
        $this->assertSame('done', $task->fresh()->status->value);
    }

    private function createMember(Project $project, User $user, ProjectMemberRole $role): ProjectMember
    {
        return ProjectMember::query()->create([
            'project_id' => $project->getKey(),
            'user_id' => $user->getKey(),
            'role' => $role,
            'joined_at' => now(),
        ]);
    }
}
