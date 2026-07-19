<?php

namespace App\Http\Controllers;

use App\Domain\Requirements\Contracts\RequirementsDocumentRepositoryInterface;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Domain\Tasks\DTOs\TaskData;
use App\Domain\Tasks\Enums\TaskStatus;
use App\Domain\Tasks\Services\TaskService;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskRepositoryInterface $tasks,
        private readonly RequirementsDocumentRepositoryInterface $requirementsDocuments,
        private readonly TaskService $taskService,
    ) {}

    public function index(Request $request, Project $project): Response
    {
        $this->authorize('view', $project);

        $project->loadMissing(['owner', 'members.user']);

        return Inertia::render('Projects/Tasks/Index', [
            'project' => ['id' => $project->id, 'name' => $project->name],
            'tasks' => $this->tasks->forProject($project->getKey())
                ->map(fn (Task $task): array => $this->taskPayload($task))
                ->values(),
            'assignableUsers' => $this->assignableUsers($project),
            'functions' => $this->currentFunctions($project),
            'canManage' => $request->user()->can('manageTasks', $project),
        ]);
    }

    public function store(StoreTaskRequest $request, Project $project): RedirectResponse
    {
        $validated = $request->validated();

        $this->taskService->create($project, $request->user(), new TaskData(
            title: $validated['title'],
            description: $validated['description'] ?? null,
            assigneeId: $validated['assignee_id'] ?? null,
            dueDate: $validated['due_date'] ?? null,
            linkedFunctionIds: $validated['linked_function_ids'] ?? [],
        ));

        return back()->with('success', 'Tâche créée.');
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task): RedirectResponse
    {
        $validated = $request->validated();

        $this->taskService->update($task, new TaskData(
            title: $validated['title'],
            description: $validated['description'] ?? null,
            assigneeId: $validated['assignee_id'] ?? null,
            dueDate: $validated['due_date'] ?? null,
            linkedFunctionIds: $validated['linked_function_ids'] ?? [],
        ));

        return back()->with('success', 'Tâche mise à jour.');
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Project $project, Task $task): RedirectResponse
    {
        $this->taskService->updateStatus(
            $project,
            $task,
            $request->user(),
            TaskStatus::from($request->validated('status')),
        );

        return back()->with('success', 'Tâche déplacée.');
    }

    public function destroy(Request $request, Project $project, Task $task): RedirectResponse
    {
        $this->authorize('manageTasks', $project);

        $this->taskService->delete($project, $task, $request->user());

        return back()->with('success', 'Tâche supprimée.');
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    private function currentFunctions(Project $project): array
    {
        $document = $this->requirementsDocuments->activeDraftForProject($project->getKey())
            ?? $this->requirementsDocuments->latestPublishedForProject($project->getKey());

        return $document?->data['step5']['functions'] ?? [];
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function assignableUsers(Project $project): array
    {
        return collect([$project->owner])
            ->merge($project->members->pluck('user'))
            ->filter()
            ->unique(fn (User $user): int => $user->getKey())
            ->map(fn (User $user): array => ['id' => $user->getKey(), 'name' => $user->name])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function taskPayload(Task $task): array
    {
        return [
            'id' => $task->getKey(),
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status->value,
            'due_date' => $task->due_date?->toDateString(),
            'linked_function_ids' => $task->linked_function_ids ?? [],
            'assignee' => $task->assignee ? ['id' => $task->assignee->getKey(), 'name' => $task->assignee->name] : null,
        ];
    }
}
