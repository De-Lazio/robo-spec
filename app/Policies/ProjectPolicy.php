<?php

namespace App\Policies;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Resource;
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

    public function inviteMembers(User $user, Project $project): bool
    {
        return $project->hasRole($user, [ProjectMemberRole::Owner, ProjectMemberRole::Manager]);
    }

    public function updateMemberRole(User $user, Project $project, ProjectMember $member): bool
    {
        return $this->canManageMember($user, $project, $member);
    }

    public function removeMember(User $user, Project $project, ProjectMember $member): bool
    {
        return $this->canManageMember($user, $project, $member);
    }

    private function canManageMember(User $user, Project $project, ProjectMember $member): bool
    {
        return $project->hasRole($user, [ProjectMemberRole::Owner, ProjectMemberRole::Manager])
            && $member->user_id !== $project->owner_id;
    }

    public function viewRequirements(User $user, Project $project): bool
    {
        return $project->hasRole($user, ProjectMemberRole::cases());
    }

    public function editRequirements(User $user, Project $project): bool
    {
        return $project->hasRole($user, [
            ProjectMemberRole::Owner,
            ProjectMemberRole::Manager,
            ProjectMemberRole::Mechanical,
            ProjectMemberRole::Electronics,
            ProjectMemberRole::Software,
            ProjectMemberRole::Contributor,
        ]);
    }

    public function publishRequirements(User $user, Project $project): bool
    {
        return $this->editRequirements($user, $project);
    }

    public function uploadResources(User $user, Project $project): bool
    {
        return $this->editRequirements($user, $project);
    }

    public function deleteResource(User $user, Project $project, Resource $resource): bool
    {
        if ($project->hasRole($user, [ProjectMemberRole::Owner, ProjectMemberRole::Manager])) {
            return true;
        }

        return $resource->uploaded_by === $user->getKey();
    }

    public function viewGithub(User $user, Project $project): bool
    {
        return $project->hasRole($user, ProjectMemberRole::cases());
    }

    public function manageGithub(User $user, Project $project): bool
    {
        return $project->hasRole($user, [ProjectMemberRole::Owner, ProjectMemberRole::Manager]);
    }

    public function manageTechnicalChoices(User $user, Project $project): bool
    {
        return $this->editRequirements($user, $project);
    }

    public function manageTasks(User $user, Project $project): bool
    {
        return $this->editRequirements($user, $project);
    }

    public function manageAlgorithmDiagrams(User $user, Project $project): bool
    {
        return $this->editRequirements($user, $project);
    }
}
