<?php

namespace App\Domain\Organizations\Contracts;

use App\Models\OrganizationInvitation;
use Illuminate\Database\Eloquent\Collection;

interface OrganizationInvitationRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): OrganizationInvitation;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateAttributes(OrganizationInvitation $invitation, array $attributes): OrganizationInvitation;

    public function findByHashedToken(string $hashedToken): ?OrganizationInvitation;

    public function findForOrganizationAndEmail(string $organizationId, string $email): ?OrganizationInvitation;

    /**
     * @return Collection<int, OrganizationInvitation>
     */
    public function pendingForOrganization(string $organizationId): Collection;

    public function markAccepted(OrganizationInvitation $invitation): OrganizationInvitation;

    public function delete(OrganizationInvitation $invitation): void;
}
