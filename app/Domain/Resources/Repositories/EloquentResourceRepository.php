<?php

namespace App\Domain\Resources\Repositories;

use App\Domain\Resources\Contracts\ResourceRepositoryInterface;
use App\Models\Resource;
use Illuminate\Support\Collection;

class EloquentResourceRepository implements ResourceRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Resource
    {
        return Resource::query()->create($attributes);
    }

    public function delete(Resource $resource): void
    {
        $resource->delete();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, resource>
     */
    public function forProject(string $projectId, array $filters = []): Collection
    {
        return Resource::query()
            ->where('project_id', $projectId)
            ->with('uploader')
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when($filters['kind'] ?? null, fn ($query, $kind) => $query->where('kind', $kind))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderByDesc('created_at')
            ->get();
    }
}
