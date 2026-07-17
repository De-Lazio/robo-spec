<?php

namespace App\Policies;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return $project->hasRole($user, ProjectMemberRole::cases());
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Project $project): bool
    {
        return $project->hasRole($user, [ProjectMemberRole::Owner, ProjectMemberRole::Manager]);
    }

    public function archive(User $user, Project $project): bool
    {
        return $project->hasRole($user, [ProjectMemberRole::Owner]);
    }

    public function restore(User $user, Project $project): bool
    {
        return $project->hasRole($user, [ProjectMemberRole::Owner]);
    }

    public function delete(User $user, Project $project): bool
    {
        return $project->hasRole($user, [ProjectMemberRole::Owner]);
    }

    public function forceDelete(User $user, Project $project): bool
    {
        return false;
    }
}
