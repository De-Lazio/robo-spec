<?php

namespace App\Http\Controllers;

use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use App\Domain\Projects\DTOs\CreateProjectData;
use App\Domain\Projects\DTOs\UpdateProjectData;
use App\Domain\Projects\Enums\ProjectStatus;
use App\Domain\Projects\Enums\RobotType;
use App\Domain\Projects\Services\ProjectService;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly ProjectService $projectService,
    ) {}

    public function dashboard(Request $request): Response
    {
        return Inertia::render('Dashboard', [
            'projects' => $this->projects->paginateForUser($request->user(), 6)->through(fn (Project $project): array => $this->projectPayload($project)),
            'stats' => $this->projects->statsForUser($request->user()),
        ]);
    }

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string'],
        ]);

        return Inertia::render('Projects/Index', [
            'projects' => $this->projects->paginateForUser($request->user(), 12, $filters)->through(fn (Project $project): array => $this->projectPayload($project)),
            'filters' => $filters,
            'statuses' => array_map(fn (ProjectStatus $status): string => $status->value, ProjectStatus::cases()),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Project::class);

        return Inertia::render('Projects/Create', [
            'robotTypes' => array_map(fn (RobotType $type): string => $type->value, RobotType::cases()),
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $project = $this->projectService->create($request->user(), new CreateProjectData(
            name: $validated['name'],
            description: $validated['description'] ?? null,
            robotType: RobotType::from($validated['robot_type']),
            domain: $validated['domain'] ?? null,
            tags: $validated['tags'] ?? [],
        ));

        return to_route('projects.show', $project)->with('success', 'Projet créé avec succès.');
    }

    public function show(Project $project): Response
    {
        $this->authorize('view', $project);

        return Inertia::render('Projects/Show', [
            'project' => $this->projectPayload($project->load(['owner', 'tags', 'members.user'])->loadCount(['activities', 'resources'])),
        ]);
    }

    public function settings(Project $project): Response
    {
        $this->authorize('view', $project);

        return Inertia::render('Projects/Settings', [
            'project' => $this->projectPayload($project->load('tags')),
            'robotTypes' => array_map(fn (RobotType $type): string => $type->value, RobotType::cases()),
            'statuses' => array_map(fn (ProjectStatus $status): string => $status->value, ProjectStatus::cases()),
            'canManage' => request()->user()->can('update', $project),
            'canArchive' => request()->user()->can('archive', $project),
            'canDelete' => request()->user()->can('delete', $project),
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $validated = $request->validated();
        $this->projectService->update($project, $request->user(), new UpdateProjectData(
            name: $validated['name'],
            description: $validated['description'] ?? null,
            robotType: RobotType::from($validated['robot_type']),
            domain: $validated['domain'] ?? null,
            status: ProjectStatus::from($validated['status']),
            tags: $validated['tags'] ?? [],
        ));

        return to_route('projects.settings', $project)->with('success', 'Informations du projet mises à jour.');
    }

    public function archive(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('archive', $project);
        $this->projectService->archive($project, $request->user());

        return to_route('projects.show', $project)->with('success', 'Projet archivé.');
    }

    public function restore(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('archive', $project);
        $this->projectService->restore($project, $request->user());

        return to_route('projects.show', $project)->with('success', 'Projet restauré.');
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);
        $this->projectService->delete($project, $request->user());

        return to_route('projects.index')->with('success', 'Projet supprimé.');
    }

    /**
     * @return array<string, mixed>
     */
    private function projectPayload(Project $project): array
    {
        return [
            'id' => $project->getKey(),
            'name' => $project->name,
            'slug' => $project->slug,
            'description' => $project->description,
            'robot_type' => $project->robot_type->value,
            'domain' => $project->domain,
            'status' => $project->status->value,
            'progress' => $project->progress,
            'archived_at' => $project->archived_at?->toDateTimeString(),
            'created_at' => $project->created_at?->toDateTimeString(),
            'updated_at' => $project->updated_at?->toDateTimeString(),
            'owner' => $project->owner ? [
                'name' => $project->owner->name,
                'email' => $project->owner->email,
            ] : null,
            'tags' => $project->relationLoaded('tags') ? $project->tags->pluck('name')->values()->all() : [],
            'members_count' => $project->relationLoaded('members') ? $project->members->count() : 1,
            'activities_count' => $project->activities_count ?? 0,
            'resources_count' => $project->resources_count ?? 0,
        ];
    }
}
