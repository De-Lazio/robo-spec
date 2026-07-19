<?php

namespace App\Domain\Projects\Repositories;

use App\Domain\Projects\Contracts\ProjectMembershipRepositoryInterface;
use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Models\ProjectMember;
use Illuminate\Database\Eloquent\Collection;

class EloquentProjectMembershipRepository implements ProjectMembershipRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): ProjectMember
    {
        return ProjectMember::query()->create($attributes);
    }

    /**
     * @return Collection<int, ProjectMember>
     */
    public function forProject(string $projectId): Collection
    {
        return ProjectMember::query()
            ->where('project_id', $projectId)
            ->with('user')
            ->orderBy('id')
            ->get();
    }

    public function updateRole(ProjectMember $member, ProjectMemberRole $role): ProjectMember
    {
        $member->update(['role' => $role]);

        return $member;
    }

    public function delete(ProjectMember $member): void
    {
        $member->delete();
    }
}
