<?php

namespace App\Domain\Projects\Repositories;

use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use App\Domain\Projects\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentProjectRepository implements ProjectRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Project
    {
        return Project::query()->create($attributes);
    }

    public function findById(string $id): ?Project
    {
        return Project::query()->find($id);
    }

    public function paginateForUser(User $user, int $perPage, array $filters = []): LengthAwarePaginator
    {
        return $this->visibleTo($user)
            ->with(['owner', 'tags'])
            ->withCount(['members', 'resources'])
            ->when($filters['search'] ?? null, fn (Builder $query, string $search): Builder => $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%");
            }))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->latest('updated_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function statsForUser(User $user): array
    {
        $query = $this->visibleTo($user);

        return [
            'total' => (clone $query)->count(),
            'in_progress' => (clone $query)->where('status', ProjectStatus::InProgress->value)->count(),
            'completed' => (clone $query)->where('status', ProjectStatus::Completed->value)->count(),
            'archived' => (clone $query)->where('status', ProjectStatus::Archived->value)->count(),
        ];
    }

    private function visibleTo(User $user): Builder
    {
        return Project::query()->where(function (Builder $query) use ($user): void {
            $query->where('owner_id', $user->getKey())
                ->orWhereHas('members', fn (Builder $query): Builder => $query->where('user_id', $user->getKey()));
        });
    }
}
