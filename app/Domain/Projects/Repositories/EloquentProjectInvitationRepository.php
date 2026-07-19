<?php

namespace App\Domain\Projects\Repositories;

use App\Domain\Projects\Contracts\ProjectInvitationRepositoryInterface;
use App\Models\ProjectInvitation;
use Illuminate\Database\Eloquent\Collection;

class EloquentProjectInvitationRepository implements ProjectInvitationRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): ProjectInvitation
    {
        return ProjectInvitation::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateAttributes(ProjectInvitation $invitation, array $attributes): ProjectInvitation
    {
        $invitation->update($attributes);

        return $invitation;
    }

    public function findByHashedToken(string $hashedToken): ?ProjectInvitation
    {
        return ProjectInvitation::query()->where('token', $hashedToken)->first();
    }

    public function findForProjectAndEmail(string $projectId, string $email): ?ProjectInvitation
    {
        return ProjectInvitation::query()
            ->where('project_id', $projectId)
            ->whereRaw('lower(email) = ?', [mb_strtolower($email)])
            ->first();
    }

    /**
     * @return Collection<int, ProjectInvitation>
     */
    public function pendingForProject(string $projectId): Collection
    {
        return ProjectInvitation::query()
            ->where('project_id', $projectId)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->orderBy('id')
            ->get();
    }

    public function markAccepted(ProjectInvitation $invitation): ProjectInvitation
    {
        $invitation->update(['accepted_at' => now()]);

        return $invitation;
    }

    public function delete(ProjectInvitation $invitation): void
    {
        $invitation->delete();
    }
}
