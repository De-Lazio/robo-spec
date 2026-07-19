<?php

namespace App\Domain\TechnicalChoices\Repositories;

use App\Domain\TechnicalChoices\Contracts\ProjectComponentRepositoryInterface;
use App\Models\ProjectComponent;
use Illuminate\Database\Eloquent\Collection;

class EloquentProjectComponentRepository implements ProjectComponentRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): ProjectComponent
    {
        return ProjectComponent::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(ProjectComponent $choice, array $attributes): ProjectComponent
    {
        $choice->update($attributes);

        return $choice;
    }

    public function delete(ProjectComponent $choice): void
    {
        $choice->delete();
    }

    /**
     * @return Collection<int, ProjectComponent>
     */
    public function forProject(string $projectId): Collection
    {
        return ProjectComponent::query()
            ->where('project_id', $projectId)
            ->with('component.category')
            ->orderByDesc('created_at')
            ->get();
    }
}
