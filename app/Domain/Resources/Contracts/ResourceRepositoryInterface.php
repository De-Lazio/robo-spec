<?php

namespace App\Domain\Resources\Contracts;

use App\Models\Resource;
use Illuminate\Support\Collection;

interface ResourceRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Resource;

    public function delete(Resource $resource): void;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Resource $resource, array $attributes): Resource;

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, resource>
     */
    public function forProject(string $projectId, array $filters = []): Collection;
}
