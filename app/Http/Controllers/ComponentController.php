<?php

namespace App\Http\Controllers;

use App\Domain\Components\Contracts\ComponentCategoryRepositoryInterface;
use App\Domain\Components\Contracts\ComponentRepositoryInterface;
use App\Domain\Components\DTOs\ComponentData;
use App\Domain\Components\Enums\ComponentType;
use App\Domain\Components\Services\ComponentLibraryService;
use App\Http\Requests\StoreComponentRequest;
use App\Http\Requests\UpdateComponentRequest;
use App\Models\Component;
use App\Models\ComponentCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComponentController extends Controller
{
    public function __construct(
        private readonly ComponentRepositoryInterface $components,
        private readonly ComponentCategoryRepositoryInterface $categories,
        private readonly ComponentLibraryService $componentLibrary,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'type' => ['nullable', 'string'],
            'category_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        return Inertia::render('Components/Index', [
            'components' => $this->components->search($filters)
                ->map(fn (Component $component): array => $this->componentPayload($component, $request))
                ->values(),
            'categories' => $this->categories->all()->map(fn (ComponentCategory $category): array => [
                'id' => $category->getKey(),
                'type' => $category->type->value,
                'name' => $category->name,
            ])->values(),
            'types' => array_map(fn (ComponentType $type): string => $type->value, ComponentType::cases()),
            'filters' => $filters,
            'canManage' => $request->user()->can('create', Component::class),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Component::class);

        return Inertia::render('Components/Create', [
            'categories' => $this->categories->all()->map(fn (ComponentCategory $category): array => [
                'id' => $category->getKey(),
                'type' => $category->type->value,
                'name' => $category->name,
            ])->values(),
            'types' => array_map(fn (ComponentType $type): string => $type->value, ComponentType::cases()),
        ]);
    }

    public function store(StoreComponentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $component = $this->componentLibrary->createComponent($request->user(), new ComponentData(
            categoryId: (int) $validated['component_category_id'],
            name: $validated['name'],
            manufacturer: $validated['manufacturer'] ?? null,
            reference: $validated['reference'] ?? null,
            description: $validated['description'] ?? null,
            specs: $this->flattenSpecs($validated['specs'] ?? []),
            datasheet: $validated['datasheet'] ?? null,
            priceCents: $validated['price_cents'] ?? null,
            currency: $validated['currency'] ?? null,
            supplierUrl: $validated['supplier_url'] ?? null,
        ));

        return to_route('components.show', $component)->with('success', 'Composant ajouté au catalogue.');
    }

    public function show(Request $request, Component $component): Response
    {
        return Inertia::render('Components/Show', [
            'component' => $this->componentPayload($component->load('category'), $request),
            'canManage' => $request->user()->can('update', $component),
        ]);
    }

    public function edit(Component $component): Response
    {
        $this->authorize('update', $component);

        return Inertia::render('Components/Edit', [
            'component' => $this->componentPayload($component->load('category'), request()),
            'categories' => $this->categories->all()->map(fn (ComponentCategory $category): array => [
                'id' => $category->getKey(),
                'type' => $category->type->value,
                'name' => $category->name,
            ])->values(),
            'types' => array_map(fn (ComponentType $type): string => $type->value, ComponentType::cases()),
        ]);
    }

    public function update(UpdateComponentRequest $request, Component $component): RedirectResponse
    {
        $validated = $request->validated();

        $this->componentLibrary->updateComponent($component, new ComponentData(
            categoryId: (int) $validated['component_category_id'],
            name: $validated['name'],
            manufacturer: $validated['manufacturer'] ?? null,
            reference: $validated['reference'] ?? null,
            description: $validated['description'] ?? null,
            specs: $this->flattenSpecs($validated['specs'] ?? []),
            datasheet: $validated['datasheet'] ?? null,
            priceCents: $validated['price_cents'] ?? null,
            currency: $validated['currency'] ?? null,
            supplierUrl: $validated['supplier_url'] ?? null,
        ));

        return to_route('components.show', $component)->with('success', 'Composant mis à jour.');
    }

    public function destroy(Component $component): RedirectResponse
    {
        $this->authorize('delete', $component);

        $this->componentLibrary->deleteComponent($component);

        return to_route('components.index')->with('success', 'Composant supprimé.');
    }

    public function toggleActive(Component $component): RedirectResponse
    {
        $this->authorize('update', $component);

        $component->is_active
            ? $this->componentLibrary->deactivateComponent($component)
            : $this->componentLibrary->reactivateComponent($component);

        return back()->with('success', $component->is_active ? 'Composant désactivé.' : 'Composant réactivé.');
    }

    public function datasheet(Component $component): StreamedResponse
    {
        abort_unless($component->datasheet_path !== null, 404);

        return Storage::disk($component->datasheet_disk)->download($component->datasheet_path, $component->datasheet_original_name);
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

    /**
     * @return array<string, mixed>
     */
    private function componentPayload(Component $component, Request $request): array
    {
        return [
            'id' => $component->getKey(),
            'name' => $component->name,
            'manufacturer' => $component->manufacturer,
            'reference' => $component->reference,
            'description' => $component->description,
            'specs' => $component->specs ?? [],
            'price_cents' => $component->price_cents,
            'currency' => $component->currency,
            'supplier_url' => $component->supplier_url,
            'is_active' => $component->is_active,
            'datasheet_url' => $component->datasheet_path ? route('components.datasheet', $component) : null,
            'category' => $component->relationLoaded('category') && $component->category ? [
                'id' => $component->category->getKey(),
                'name' => $component->category->name,
                'type' => $component->category->type->value,
            ] : null,
        ];
    }
}
