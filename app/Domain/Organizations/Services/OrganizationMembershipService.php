<?php

namespace App\Domain\Organizations\Services;

use App\Domain\Organizations\Contracts\OrganizationMembershipRepositoryInterface;
use App\Domain\Organizations\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationMember;
use Illuminate\Validation\ValidationException;

class OrganizationMembershipService
{
    public function __construct(
        private readonly OrganizationMembershipRepositoryInterface $memberships,
    ) {}

    public function updateRole(Organization $organization, OrganizationMember $member, OrganizationRole $role): OrganizationMember
    {
        $this->guardNotOwnerRow($organization, $member);

        return $this->memberships->updateRole($member, $role);
    }

    public function remove(Organization $organization, OrganizationMember $member): void
    {
        $this->guardNotOwnerRow($organization, $member);

        $this->memberships->delete($member);
    }

    private function guardNotOwnerRow(Organization $organization, OrganizationMember $member): void
    {
        if ($member->user_id === $organization->owner_id) {
            throw ValidationException::withMessages([
                'role' => 'Le propriétaire de l’organisation ne peut pas être rétrogradé ni retiré.',
            ]);
        }
    }
}
