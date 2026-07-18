<?php

namespace App\Domain\Integrations\GitHub\Contracts;

use App\Models\GithubRepository;

interface GithubLinkRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): GithubRepository;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(GithubRepository $repository, array $attributes): GithubRepository;

    public function delete(GithubRepository $repository): void;

    public function forProject(string $projectId): ?GithubRepository;
}
