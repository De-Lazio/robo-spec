<?php

namespace App\Domain\TechnicalChoices\Contracts;

use App\Models\ProjectComponent;
use Illuminate\Database\Eloquent\Collection;

interface ProjectComponentRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): ProjectComponent;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(ProjectComponent $choice, array $attributes): ProjectComponent;

    public function delete(ProjectComponent $choice): void;

    /**
     * @return Collection<int, ProjectComponent>
     */
    public function forProject(string $projectId): Collection;
}
