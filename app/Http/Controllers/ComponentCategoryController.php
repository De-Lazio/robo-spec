<?php

namespace App\Http\Controllers;

use App\Domain\Components\Contracts\ComponentCategoryRepositoryInterface;
use App\Domain\Components\DTOs\CreateComponentCategoryData;
use App\Domain\Components\Enums\ComponentType;
use App\Domain\Components\Services\ComponentLibraryService;
use App\Http\Requests\StoreComponentCategoryRequest;
use App\Http\Requests\UpdateComponentCategoryRequest;
use App\Models\ComponentCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ComponentCategoryController extends Controller
{
    public function __construct(
        private readonly ComponentCategoryRepositoryInterface $categories,
        private readonly ComponentLibraryService $componentLibrary,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Components/Categories/Index', [
            'categories' => $this->categories->all()->map(fn (ComponentCategory $category): array => $this->categoryPayload($category))->values(),
            'types' => array_map(fn (ComponentType $type): string => $type->value, ComponentType::cases()),
            'canManage' => $request->user()->can('create', ComponentCategory::class),
        ]);
    }

    public function store(StoreComponentCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->componentLibrary->createCategory(new CreateComponentCategoryData(
            type: ComponentType::from($validated['type']),
            name: $validated['name'],
        ));

        return back()->with('success', 'Catégorie créée.');
    }

    public function update(UpdateComponentCategoryRequest $request, ComponentCategory $category): RedirectResponse
    {
        $this->componentLibrary->updateCategory($category, $request->validated('name'));

        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(Request $request, ComponentCategory $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $this->componentLibrary->deleteCategory($category);

        return back()->with('success', 'Catégorie supprimée.');
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryPayload(ComponentCategory $category): array
    {
        return [
            'id' => $category->getKey(),
            'type' => $category->type->value,
            'name' => $category->name,
            'slug' => $category->slug,
            'components_count' => $category->components_count ?? 0,
        ];
    }
}
