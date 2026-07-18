<?php

namespace App\Domain\Components\Repositories;

use App\Domain\Components\Contracts\ComponentCategoryRepositoryInterface;
use App\Models\ComponentCategory;
use Illuminate\Database\Eloquent\Collection;

class EloquentComponentCategoryRepository implements ComponentCategoryRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): ComponentCategory
    {
        return ComponentCategory::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(ComponentCategory $category, array $attributes): ComponentCategory
    {
        $category->update($attributes);

        return $category;
    }

    public function delete(ComponentCategory $category): void
    {
        $category->delete();
    }

    /**
     * @return Collection<int, ComponentCategory>
     */
    public function all(): Collection
    {
        return ComponentCategory::query()
            ->withCount('components')
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }
}
