<?php

namespace App\Domain\Organizations\Repositories;

use App\Domain\Organizations\Contracts\OrganizationRepositoryInterface;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EloquentOrganizationRepository implements OrganizationRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Organization
    {
        return Organization::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Organization $organization, array $attributes): Organization
    {
        $organization->update($attributes);

        return $organization;
    }

    public function delete(Organization $organization): void
    {
        $organization->delete();
    }

    /**
     * @return Collection<int, Organization>
     */
    public function forUser(User $user): Collection
    {
        return Organization::query()
            ->where(function (Builder $query) use ($user): void {
                $query->where('owner_id', $user->getKey())
                    ->orWhereHas('members', fn (Builder $query): Builder => $query->where('user_id', $user->getKey()));
            })
            ->withCount(['members', 'projects'])
            ->with('owner')
            ->orderBy('name')
            ->get();
    }
}
