<?php

namespace App\Http\Controllers;

use App\Domain\Components\Contracts\ComponentRepositoryInterface;
use App\Domain\Requirements\Contracts\RequirementsDocumentRepositoryInterface;
use App\Domain\TechnicalChoices\Contracts\ProjectComponentRepositoryInterface;
use App\Domain\TechnicalChoices\DTOs\ProjectComponentData;
use App\Domain\TechnicalChoices\Services\TechnicalChoiceService;
use App\Http\Requests\StoreProjectComponentRequest;
use App\Http\Requests\UpdateProjectComponentRequest;
use App\Models\Component;
use App\Models\Project;
use App\Models\ProjectComponent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TechnicalChoiceController extends Controller
{
    public function __construct(
        private readonly ProjectComponentRepositoryInterface $choices,
        private readonly ComponentRepositoryInterface $components,
        private readonly RequirementsDocumentRepositoryInterface $requirementsDocuments,
        private readonly TechnicalChoiceService $technicalChoiceService,
    ) {}

    public function index(Request $request, Project $project): Response
    {
        $this->authorize('view', $project);

        $choices = $this->choices->forProject($project->getKey());

        return Inertia::render('Projects/TechnicalChoices/Index', [
            'project' => ['id' => $project->id, 'name' => $project->name],
            'choices' => $choices->map(fn (ProjectComponent $choice): array => $this->choicePayload($choice))->values(),
            'totalCostCents' => $choices->sum(fn (ProjectComponent $choice): int => ($choice->component->price_cents ?? 0) * $choice->quantity),
            'availableComponents' => $this->components->search(['is_active' => true])
                ->map(fn (Component $component): array => [
                    'id' => $component->getKey(),
                    'name' => $component->name,
                    'manufacturer' => $component->manufacturer,
                    'category' => ['id' => $component->category->getKey(), 'name' => $component->category->name, 'type' => $component->category->type->value],
                ])
                ->values(),
            'functions' => $this->currentFunctions($project),
            'canManage' => $request->user()->can('manageTechnicalChoices', $project),
        ]);
    }

    public function store(StoreProjectComponentRequest $request, Project $project): RedirectResponse
    {
        $validated = $request->validated();

        $this->technicalChoiceService->addComponent($project, $request->user(), new ProjectComponentData(
            componentId: $validated['component_id'],
            quantity: $validated['quantity'],
            rationale: $validated['rationale'] ?? null,
            linkedFunctionIds: $validated['linked_function_ids'] ?? [],
        ));

        return back()->with('success', 'Composant ajouté aux choix techniques.');
    }

    public function update(UpdateProjectComponentRequest $request, Project $project, ProjectComponent $technicalChoice): RedirectResponse
    {
        $validated = $request->validated();

        $this->technicalChoiceService->updateComponent($technicalChoice, new ProjectComponentData(
            componentId: $technicalChoice->component_id,
            quantity: $validated['quantity'],
            rationale: $validated['rationale'] ?? null,
            linkedFunctionIds: $validated['linked_function_ids'] ?? [],
        ));

        return back()->with('success', 'Choix technique mis à jour.');
    }

    public function destroy(Request $request, Project $project, ProjectComponent $technicalChoice): RedirectResponse
    {
        $this->authorize('manageTechnicalChoices', $project);

        $this->technicalChoiceService->removeComponent($project, $technicalChoice, $request->user());

        return back()->with('success', 'Composant retiré des choix techniques.');
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
     * @return array<string, mixed>
     */
    private function choicePayload(ProjectComponent $choice): array
    {
        return [
            'id' => $choice->getKey(),
            'quantity' => $choice->quantity,
            'rationale' => $choice->rationale,
            'linked_function_ids' => $choice->linked_function_ids ?? [],
            'component' => [
                'id' => $choice->component->getKey(),
                'name' => $choice->component->name,
                'manufacturer' => $choice->component->manufacturer,
                'price_cents' => $choice->component->price_cents,
                'currency' => $choice->component->currency,
                'is_active' => $choice->component->is_active,
                'category' => [
                    'id' => $choice->component->category->getKey(),
                    'name' => $choice->component->category->name,
                    'type' => $choice->component->category->type->value,
                ],
            ],
        ];
    }
}
