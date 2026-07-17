<?php

namespace App\Domain\Projects\Contracts;

use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProjectRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Project;

    public function findById(string $id): ?Project;

    /**
     * @param  array<string, string>  $filters
     * @return LengthAwarePaginator<int, Project>
     */
    public function paginateForUser(User $user, int $perPage, array $filters = []): LengthAwarePaginator;

    /**
     * @return array{total: int, in_progress: int, completed: int, archived: int}
     */
    public function statsForUser(User $user): array;
}
