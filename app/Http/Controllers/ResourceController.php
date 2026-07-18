<?php

namespace App\Http\Controllers;

use App\Domain\Resources\Contracts\ResourceRepositoryInterface;
use App\Domain\Resources\DTOs\UploadResourceData;
use App\Domain\Resources\Enums\ResourceCategory;
use App\Domain\Resources\Enums\ResourceKind;
use App\Domain\Resources\Services\ResourceService;
use App\Http\Requests\StoreResourceRequest;
use App\Models\Project;
use App\Models\Resource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResourceController extends Controller
{
    public function __construct(
        private readonly ResourceRepositoryInterface $resources,
        private readonly ResourceService $resourceService,
    ) {}

    public function index(Request $request, Project $project): Response
    {
        $this->authorize('view', $project);

        $filters = $request->validate([
            'category' => ['nullable', 'string'],
            'kind' => ['nullable', 'string'],
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        $canUpload = $request->user()->can('uploadResources', $project);

        return Inertia::render('Projects/Resources', [
            'project' => ['id' => $project->id, 'name' => $project->name],
            'resources' => $this->resources->forProject($project->getKey(), $filters)
                ->map(fn (Resource $resource): array => $this->resourcePayload($project, $resource, $request))
                ->values(),
            'filters' => $filters,
            'categories' => array_map(fn (ResourceCategory $case): string => $case->value, ResourceCategory::cases()),
            'kinds' => array_map(fn (ResourceKind $case): string => $case->value, ResourceKind::cases()),
            'canUpload' => $canUpload,
        ]);
    }

    public function store(StoreResourceRequest $request, Project $project): RedirectResponse
    {
        $validated = $request->validated();

        $this->resourceService->upload($project, $request->user(), new UploadResourceData(
            file: $validated['file'],
            category: ResourceCategory::from($validated['category']),
            description: $validated['description'] ?? null,
        ));

        return back()->with('success', 'Ressource ajoutée.');
    }

    public function download(Project $project, Resource $resource): StreamedResponse
    {
        $this->authorize('view', $project);

        return Storage::disk($resource->disk)->download($resource->path, $resource->original_name);
    }

    public function preview(Project $project, Resource $resource): StreamedResponse
    {
        $this->authorize('view', $project);

        abort_unless(in_array($resource->kind, [ResourceKind::Image, ResourceKind::Document], true), 404);

        return Storage::disk($resource->disk)->response($resource->path, $resource->original_name);
    }

    public function destroy(Request $request, Project $project, Resource $resource): RedirectResponse
    {
        $this->authorize('deleteResource', [$project, $resource]);

        $this->resourceService->delete($resource, $request->user());

        return back()->with('success', 'Ressource supprimée.');
    }

    /**
     * @return array<string, mixed>
     */
    private function resourcePayload(Project $project, Resource $resource, Request $request): array
    {
        return [
            'id' => $resource->getKey(),
            'name' => $resource->name,
            'original_name' => $resource->original_name,
            'category' => $resource->category->value,
            'kind' => $resource->kind->value,
            'size_bytes' => $resource->size_bytes,
            'description' => $resource->description,
            'created_at' => $resource->created_at?->toDateTimeString(),
            'uploader' => $resource->uploader ? ['name' => $resource->uploader->name] : null,
            'can_delete' => $request->user()->can('deleteResource', [$project, $resource]),
            'download_url' => route('projects.resources.download', [$project, $resource]),
            'preview_url' => in_array($resource->kind, [ResourceKind::Image, ResourceKind::Document], true)
                ? route('projects.resources.preview', [$project, $resource])
                : null,
        ];
    }
}
