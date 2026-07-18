<?php

namespace App\Domain\Organizations\Contracts;

use App\Domain\Organizations\Enums\OrganizationRole;
use App\Models\OrganizationMember;
use Illuminate\Database\Eloquent\Collection;

interface OrganizationMembershipRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): OrganizationMember;

    /**
     * @return Collection<int, OrganizationMember>
     */
    public function forOrganization(string $organizationId): Collection;

    public function updateRole(OrganizationMember $member, OrganizationRole $role): OrganizationMember;

    public function delete(OrganizationMember $member): void;
}
