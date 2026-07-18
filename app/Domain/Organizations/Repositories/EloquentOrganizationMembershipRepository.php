<?php

namespace App\Domain\Organizations\Repositories;

use App\Domain\Organizations\Contracts\OrganizationMembershipRepositoryInterface;
use App\Domain\Organizations\Enums\OrganizationRole;
use App\Models\OrganizationMember;
use Illuminate\Database\Eloquent\Collection;

class EloquentOrganizationMembershipRepository implements OrganizationMembershipRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): OrganizationMember
    {
        return OrganizationMember::query()->create($attributes);
    }

    /**
     * @return Collection<int, OrganizationMember>
     */
    public function forOrganization(string $organizationId): Collection
    {
        return OrganizationMember::query()
            ->where('organization_id', $organizationId)
            ->with('user')
            ->orderBy('id')
            ->get();
    }

    public function updateRole(OrganizationMember $member, OrganizationRole $role): OrganizationMember
    {
        $member->update(['role' => $role]);

        return $member;
    }

    public function delete(OrganizationMember $member): void
    {
        $member->delete();
    }
}
