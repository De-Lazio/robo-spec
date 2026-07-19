<?php

namespace App\Domain\Integrations\GitHub\Repositories;

use App\Domain\Integrations\GitHub\Contracts\GithubLinkRepositoryInterface;
use App\Models\GithubRepository;

class EloquentGithubLinkRepository implements GithubLinkRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): GithubRepository
    {
        return GithubRepository::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(GithubRepository $repository, array $attributes): GithubRepository
    {
        $repository->update($attributes);

        return $repository;
    }

    public function delete(GithubRepository $repository): void
    {
        $repository->delete();
    }

    public function forProject(string $projectId): ?GithubRepository
    {
        return GithubRepository::query()->where('project_id', $projectId)->first();
    }
}
