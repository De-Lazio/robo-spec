<?php

namespace App\Domain\Projects\Contracts;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Models\ProjectMember;
use Illuminate\Database\Eloquent\Collection;

interface ProjectMembershipRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): ProjectMember;

    /**
     * @return Collection<int, ProjectMember>
     */
    public function forProject(string $projectId): Collection;

    public function updateRole(ProjectMember $member, ProjectMemberRole $role): ProjectMember;

    public function delete(ProjectMember $member): void;
}
