<?php

namespace App\Domain\Organizations\Repositories;

use App\Domain\Organizations\Contracts\OrganizationInvitationRepositoryInterface;
use App\Models\OrganizationInvitation;
use Illuminate\Database\Eloquent\Collection;

class EloquentOrganizationInvitationRepository implements OrganizationInvitationRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): OrganizationInvitation
    {
        return OrganizationInvitation::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateAttributes(OrganizationInvitation $invitation, array $attributes): OrganizationInvitation
    {
        $invitation->update($attributes);

        return $invitation;
    }

    public function findByHashedToken(string $hashedToken): ?OrganizationInvitation
    {
        return OrganizationInvitation::query()->where('token', $hashedToken)->first();
    }

    public function findForOrganizationAndEmail(string $organizationId, string $email): ?OrganizationInvitation
    {
        return OrganizationInvitation::query()
            ->where('organization_id', $organizationId)
            ->whereRaw('lower(email) = ?', [mb_strtolower($email)])
            ->first();
    }

    /**
     * @return Collection<int, OrganizationInvitation>
     */
    public function pendingForOrganization(string $organizationId): Collection
    {
        return OrganizationInvitation::query()
            ->where('organization_id', $organizationId)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->orderBy('id')
            ->get();
    }

    public function markAccepted(OrganizationInvitation $invitation): OrganizationInvitation
    {
        $invitation->update(['accepted_at' => now()]);

        return $invitation;
    }

    public function delete(OrganizationInvitation $invitation): void
    {
        $invitation->delete();
    }
}
