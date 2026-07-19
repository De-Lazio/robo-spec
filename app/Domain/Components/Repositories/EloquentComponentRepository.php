<?php

namespace App\Domain\Components\Repositories;

use App\Domain\Components\Contracts\ComponentRepositoryInterface;
use App\Models\Component;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EloquentComponentRepository implements ComponentRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Component
    {
        return Component::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Component $component, array $attributes): Component
    {
        $component->update($attributes);

        return $component;
    }

    public function delete(Component $component): void
    {
        $component->delete();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Component>
     */
    public function search(array $filters = []): Collection
    {
        return Component::query()
            ->with('category')
            ->when($filters['type'] ?? null, fn (Builder $query, string $type) => $query->whereHas('category', fn (Builder $query) => $query->where('type', $type)))
            ->when($filters['category_id'] ?? null, fn (Builder $query, int $categoryId) => $query->where('component_category_id', $categoryId))
            ->when(array_key_exists('is_active', $filters), fn (Builder $query) => $query->where('is_active', $filters['is_active']))
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('manufacturer', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%");
            }))
            ->where(function (Builder $query) use ($filters): void {
                $query->whereNull('owner_project_id');

                if ($filters['project_id'] ?? null) {
                    $query->orWhere('owner_project_id', $filters['project_id']);
                }
            })
            ->orderBy('name')
            ->get();
    }
}
