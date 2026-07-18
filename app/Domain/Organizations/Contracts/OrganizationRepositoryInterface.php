<?php

namespace App\Domain\Organizations\Contracts;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface OrganizationRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Organization;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Organization $organization, array $attributes): Organization;

    public function delete(Organization $organization): void;

    /**
     * @return Collection<int, Organization>
     */
    public function forUser(User $user): Collection;
}
