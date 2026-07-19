<?php

namespace App\Http\Controllers;

use App\Domain\Components\DTOs\ComponentData;
use App\Domain\Components\Services\ComponentLibraryService;
use App\Http\Requests\StoreLocalComponentRequest;
use App\Http\Requests\UpdateLocalComponentRequest;
use App\Models\Component;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectComponentController extends Controller
{
    public function __construct(
        private readonly ComponentLibraryService $componentLibrary,
    ) {}

    public function store(StoreLocalComponentRequest $request, Project $project): RedirectResponse
    {
        $validated = $request->validated();

        $this->componentLibrary->createLocalComponent($project, $request->user(), new ComponentData(
            categoryId: (int) $validated['component_category_id'],
            name: $validated['name'],
            manufacturer: $validated['manufacturer'] ?? null,
            reference: $validated['reference'] ?? null,
            description: $validated['description'] ?? null,
            specs: $this->flattenSpecs($validated['specs'] ?? []),
            datasheet: null,
            priceCents: $validated['price_cents'] ?? null,
            currency: $validated['currency'] ?? null,
            supplierUrl: $validated['supplier_url'] ?? null,
        ));

        return back()->with('success', 'Composant local créé. Sélectionnez-le pour l’ajouter aux choix techniques.');
    }

    public function update(UpdateLocalComponentRequest $request, Project $project, Component $localComponent): RedirectResponse
    {
        $validated = $request->validated();

        $this->componentLibrary->updateComponent($localComponent, new ComponentData(
            categoryId: (int) $validated['component_category_id'],
            name: $validated['name'],
            manufacturer: $validated['manufacturer'] ?? null,
            reference: $validated['reference'] ?? null,
            description: $validated['description'] ?? null,
            specs: $this->flattenSpecs($validated['specs'] ?? []),
            datasheet: null,
            priceCents: $validated['price_cents'] ?? null,
            currency: $validated['currency'] ?? null,
            supplierUrl: $validated['supplier_url'] ?? null,
        ));

        return back()->with('success', 'Composant local mis à jour.');
    }

    public function destroy(Request $request, Project $project, Component $localComponent): RedirectResponse
    {
        abort_unless($localComponent->owner_project_id === $project->getKey(), 404);
        $this->authorize('manageTechnicalChoices', $project);

        $this->componentLibrary->deleteComponent($localComponent);

        return back()->with('success', 'Composant local supprimé.');
    }

    /**
     * @param  array<int, array{key: string, value: string}>  $specs
     * @return array<string, string>
     */
    private function flattenSpecs(array $specs): array
    {
        $flattened = [];

        foreach ($specs as $spec) {
            if (($spec['key'] ?? '') !== '') {
                $flattened[$spec['key']] = $spec['value'] ?? '';
            }
        }

        return $flattened;
    }
}
