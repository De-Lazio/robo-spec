<?php

namespace App\Domain\Projects\Services;

use App\Domain\Projects\Contracts\ProjectActivityRepositoryInterface;
use App\Domain\Projects\Contracts\ProjectMembershipRepositoryInterface;
use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectMembershipService
{
    public function __construct(
        private readonly ProjectMembershipRepositoryInterface $memberships,
        private readonly ProjectActivityRepositoryInterface $activities,
    ) {}

    public function updateRole(Project $project, ProjectMember $member, User $actor, ProjectMemberRole $role): ProjectMember
    {
        $this->guardNotOwnerRow($project, $member);

        return DB::transaction(function () use ($project, $member, $actor, $role): ProjectMember {
            $member = $this->memberships->updateRole($member, $role);

            $this->activities->create([
                'project_id' => $project->getKey(),
                'actor_id' => $actor->getKey(),
                'event' => 'member.role_updated',
                'subject_type' => ProjectMember::class,
                'subject_id' => $member->getKey(),
                'properties' => ['role' => $role->value],
            ]);

            return $member;
        });
    }

    public function remove(Project $project, ProjectMember $member, User $actor): void
    {
        $this->guardNotOwnerRow($project, $member);

        DB::transaction(function () use ($project, $member, $actor): void {
            $this->activities->create([
                'project_id' => $project->getKey(),
                'actor_id' => $actor->getKey(),
                'event' => 'member.removed',
                'subject_type' => ProjectMember::class,
                'subject_id' => $member->getKey(),
                'properties' => ['user_id' => $member->user_id],
            ]);

            $this->memberships->delete($member);
        });
    }

    private function guardNotOwnerRow(Project $project, ProjectMember $member): void
    {
        if ($member->user_id === $project->owner_id) {
            throw ValidationException::withMessages([
                'role' => 'Le propriétaire du projet ne peut pas être rétrogradé ni retiré.',
            ]);
        }
    }
}
