<?php

namespace App\Http\Controllers;

use App\Domain\Organizations\Contracts\OrganizationRepositoryInterface;
use App\Domain\Organizations\DTOs\CreateOrganizationData;
use App\Domain\Organizations\Services\OrganizationService;
use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Models\Organization;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    public function __construct(
        private readonly OrganizationRepositoryInterface $organizations,
        private readonly OrganizationService $organizationService,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Organizations/Index', [
            'organizations' => $this->organizations->forUser($request->user())
                ->map(fn (Organization $organization): array => $this->organizationPayload($organization))
                ->values(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Organization::class);

        return Inertia::render('Organizations/Create');
    }

    public function store(StoreOrganizationRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $organization = $this->organizationService->create($request->user(), new CreateOrganizationData(
            name: $validated['name'],
            description: $validated['description'] ?? null,
        ));

        return to_route('organizations.show', $organization)->with('success', 'Organisation créée avec succès.');
    }

    public function show(Organization $organization): Response
    {
        $this->authorize('view', $organization);

        return Inertia::render('Organizations/Show', [
            'organization' => $this->organizationPayload($organization->load('owner')->loadCount(['members', 'projects'])),
            'projects' => $organization->projects()
                ->with(['owner', 'tags'])
                ->withCount(['members', 'resources', 'activities'])
                ->latest('updated_at')
                ->get()
                ->map(fn (Project $project): array => $this->projectPayload($project, $organization))
                ->values(),
            'canManage' => request()->user()->can('update', $organization),
        ]);
    }

    public function settings(Organization $organization): Response
    {
        $this->authorize('view', $organization);

        return Inertia::render('Organizations/Settings', [
            'organization' => $this->organizationPayload($organization),
            'canManage' => request()->user()->can('update', $organization),
            'canDelete' => request()->user()->can('delete', $organization),
        ]);
    }

    public function update(UpdateOrganizationRequest $request, Organization $organization): RedirectResponse
    {
        $validated = $request->validated();

        $this->organizationService->update($organization, $validated['name'], $validated['description'] ?? null);

        return to_route('organizations.settings', $organization)->with('success', 'Organisation mise à jour.');
    }

    public function destroy(Organization $organization): RedirectResponse
    {
        $this->authorize('delete', $organization);

        $this->organizationService->delete($organization);

        return to_route('organizations.index')->with('success', 'Organisation supprimée.');
    }

    /**
     * @return array<string, mixed>
     */
    private function organizationPayload(Organization $organization): array
    {
        return [
            'id' => $organization->getKey(),
            'name' => $organization->name,
            'slug' => $organization->slug,
            'description' => $organization->description,
            'created_at' => $organization->created_at?->toDateTimeString(),
            'owner' => $organization->relationLoaded('owner') && $organization->owner ? [
                'name' => $organization->owner->name,
                'email' => $organization->owner->email,
            ] : null,
            'members_count' => $organization->members_count ?? 1,
            'projects_count' => $organization->projects_count ?? 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function projectPayload(Project $project, Organization $organization): array
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
            'organization_id' => $organization->getKey(),
            'organization' => ['id' => $organization->getKey(), 'name' => $organization->name],
            'tags' => $project->relationLoaded('tags') ? $project->tags->pluck('name')->values()->all() : [],
            'members_count' => $project->members_count ?? 1,
            'activities_count' => $project->activities_count ?? 0,
            'resources_count' => $project->resources_count ?? 0,
        ];
    }
}
