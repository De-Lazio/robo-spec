<?php

namespace App\Domain\Projects\Contracts;

use App\Models\ProjectInvitation;
use Illuminate\Database\Eloquent\Collection;

interface ProjectInvitationRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): ProjectInvitation;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateAttributes(ProjectInvitation $invitation, array $attributes): ProjectInvitation;

    public function findByHashedToken(string $hashedToken): ?ProjectInvitation;

    public function findForProjectAndEmail(string $projectId, string $email): ?ProjectInvitation;

    /**
     * @return Collection<int, ProjectInvitation>
     */
    public function pendingForProject(string $projectId): Collection;

    public function markAccepted(ProjectInvitation $invitation): ProjectInvitation;

    public function delete(ProjectInvitation $invitation): void;
}
