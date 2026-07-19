<?php

namespace App\Http\Controllers;

use App\Domain\AlgorithmDiagrams\Contracts\AlgorithmDiagramRepositoryInterface;
use App\Domain\AlgorithmDiagrams\DTOs\AlgorithmDiagramData;
use App\Domain\AlgorithmDiagrams\Enums\DiagramFormalism;
use App\Domain\AlgorithmDiagrams\Services\AlgorithmDiagramService;
use App\Domain\Resources\DTOs\UploadResourceData;
use App\Domain\Resources\Enums\ResourceCategory;
use App\Domain\Resources\Services\ResourceService;
use App\Domain\TechnicalChoices\Contracts\ProjectComponentRepositoryInterface;
use App\Http\Requests\ExportAlgorithmDiagramRequest;
use App\Http\Requests\StoreAlgorithmDiagramRequest;
use App\Http\Requests\UpdateAlgorithmDiagramRequest;
use App\Models\AlgorithmDiagram;
use App\Models\Project;
use App\Models\ProjectComponent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AlgorithmDiagramController extends Controller
{
    public function __construct(
        private readonly AlgorithmDiagramRepositoryInterface $diagrams,
        private readonly ProjectComponentRepositoryInterface $choices,
        private readonly AlgorithmDiagramService $diagramService,
        private readonly ResourceService $resourceService,
    ) {}

    public function index(Request $request, Project $project): Response
    {
        $this->authorize('view', $project);

        return Inertia::render('Projects/AlgorithmDiagrams/Index', [
            'project' => ['id' => $project->id, 'name' => $project->name],
            'diagrams' => $this->diagrams->forProject($project->getKey())
                ->map(fn (AlgorithmDiagram $diagram): array => [
                    'id' => $diagram->getKey(),
                    'name' => $diagram->name,
                    'formalism' => $diagram->formalism->value,
                    'updated_at' => $diagram->updated_at?->toDateTimeString(),
                ])
                ->values(),
            'canManage' => $request->user()->can('manageAlgorithmDiagrams', $project),
        ]);
    }

    public function store(StoreAlgorithmDiagramRequest $request, Project $project): RedirectResponse
    {
        $validated = $request->validated();

        $diagram = $this->diagramService->create(
            $project,
            $request->user(),
            $validated['name'],
            DiagramFormalism::from($validated['formalism']),
        );

        return to_route('projects.algorithm-diagrams.edit', [$project, $diagram]);
    }

    public function edit(Request $request, Project $project, AlgorithmDiagram $algorithmDiagram): Response
    {
        $this->authorize('view', $project);

        return Inertia::render('Projects/AlgorithmDiagrams/Edit', [
            'project' => ['id' => $project->id, 'name' => $project->name],
            'diagram' => [
                'id' => $algorithmDiagram->getKey(),
                'name' => $algorithmDiagram->name,
                'formalism' => $algorithmDiagram->formalism->value,
                'data' => $algorithmDiagram->data,
                'microcontroller_component_id' => $algorithmDiagram->microcontroller_component_id,
                'energy_source_component_id' => $algorithmDiagram->energy_source_component_id,
            ],
            'availableComponents' => $this->choices->forProject($project->getKey())
                ->map(fn (ProjectComponent $choice): array => [
                    'id' => $choice->getKey(),
                    'name' => $choice->component->name,
                    'manufacturer' => $choice->component->manufacturer,
                    'category' => [
                        'id' => $choice->component->category->getKey(),
                        'name' => $choice->component->category->name,
                        'type' => $choice->component->category->type->value,
                    ],
                ])
                ->values(),
            'canManage' => $request->user()->can('manageAlgorithmDiagrams', $project),
        ]);
    }

    public function update(UpdateAlgorithmDiagramRequest $request, Project $project, AlgorithmDiagram $algorithmDiagram): RedirectResponse
    {
        $validated = $request->validated();

        $this->diagramService->save($algorithmDiagram, $request->user(), new AlgorithmDiagramData(
            name: $validated['name'],
            data: $validated['data'],
            microcontrollerComponentId: $validated['microcontroller_component_id'] ?? null,
            energySourceComponentId: $validated['energy_source_component_id'] ?? null,
        ));

        return back()->with('success', 'Diagramme enregistré.');
    }

    public function destroy(Request $request, Project $project, AlgorithmDiagram $algorithmDiagram): RedirectResponse
    {
        $this->authorize('manageAlgorithmDiagrams', $project);

        $this->diagramService->delete($project, $algorithmDiagram, $request->user());

        return to_route('projects.algorithm-diagrams.index', $project)->with('success', 'Diagramme supprimé.');
    }

    public function export(ExportAlgorithmDiagramRequest $request, Project $project, AlgorithmDiagram $algorithmDiagram): RedirectResponse
    {
        $validated = $request->validated();

        $this->resourceService->upload($project, $request->user(), new UploadResourceData(
            file: $validated['file'],
            category: ResourceCategory::from($validated['category']),
            description: "Export du diagramme « {$algorithmDiagram->name} »",
            algorithmDiagramId: $algorithmDiagram->getKey(),
        ));

        return back()->with('success', 'Diagramme exporté vers les Ressources.');
    }
}
