<?php

namespace App\Domain\AlgorithmDiagrams\Contracts;

use App\Models\AlgorithmDiagram;
use Illuminate\Database\Eloquent\Collection;

interface AlgorithmDiagramRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): AlgorithmDiagram;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(AlgorithmDiagram $diagram, array $attributes): AlgorithmDiagram;

    public function delete(AlgorithmDiagram $diagram): void;

    /**
     * @return Collection<int, AlgorithmDiagram>
     */
    public function forProject(string $projectId): Collection;
}
