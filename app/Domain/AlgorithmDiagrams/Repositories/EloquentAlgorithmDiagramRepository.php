<?php

namespace App\Domain\AlgorithmDiagrams\Repositories;

use App\Domain\AlgorithmDiagrams\Contracts\AlgorithmDiagramRepositoryInterface;
use App\Models\AlgorithmDiagram;
use Illuminate\Database\Eloquent\Collection;

class EloquentAlgorithmDiagramRepository implements AlgorithmDiagramRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): AlgorithmDiagram
    {
        return AlgorithmDiagram::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(AlgorithmDiagram $diagram, array $attributes): AlgorithmDiagram
    {
        $diagram->update($attributes);

        return $diagram;
    }

    public function delete(AlgorithmDiagram $diagram): void
    {
        $diagram->delete();
    }

    /**
     * @return Collection<int, AlgorithmDiagram>
     */
    public function forProject(string $projectId): Collection
    {
        return AlgorithmDiagram::query()
            ->where('project_id', $projectId)
            ->orderBy('created_at')
            ->get();
    }
}
