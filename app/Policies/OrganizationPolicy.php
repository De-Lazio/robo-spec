<?php

namespace App\Policies;

use App\Domain\Organizations\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;

class OrganizationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Organization $organization): bool
    {
        return $organization->hasRole($user, OrganizationRole::cases());
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Organization $organization): bool
    {
        return $organization->hasRole($user, [OrganizationRole::Owner, OrganizationRole::Admin]);
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $organization->hasRole($user, [OrganizationRole::Owner]);
    }

    public function inviteMembers(User $user, Organization $organization): bool
    {
        return $organization->hasRole($user, [OrganizationRole::Owner, OrganizationRole::Admin]);
    }

    public function updateMemberRole(User $user, Organization $organization, OrganizationMember $member): bool
    {
        return $this->canManageMember($user, $organization, $member);
    }

    public function removeMember(User $user, Organization $organization, OrganizationMember $member): bool
    {
        return $this->canManageMember($user, $organization, $member);
    }

    public function createProjectUnder(User $user, Organization $organization): bool
    {
        return $organization->hasRole($user, [OrganizationRole::Owner, OrganizationRole::Admin]);
    }

    private function canManageMember(User $user, Organization $organization, OrganizationMember $member): bool
    {
        return $organization->hasRole($user, [OrganizationRole::Owner, OrganizationRole::Admin])
            && $member->user_id !== $organization->owner_id;
    }
}
