<?php

namespace App\Domain\Projects\Services;

use App\Domain\Notifications\Services\NotificationService;
use App\Domain\Projects\Contracts\ProjectActivityRepositoryInterface;
use App\Domain\Projects\Contracts\ProjectInvitationRepositoryInterface;
use App\Domain\Projects\Contracts\ProjectMembershipRepositoryInterface;
use App\Domain\Projects\DTOs\InviteMemberData;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\ProjectMember;
use App\Models\User;
use App\Notifications\ProjectInvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProjectInvitationService
{
    public function __construct(
        private readonly ProjectInvitationRepositoryInterface $invitations,
        private readonly ProjectMembershipRepositoryInterface $memberships,
        private readonly ProjectActivityRepositoryInterface $activities,
        private readonly NotificationService $notifications,
    ) {}

    public function invite(Project $project, User $actor, InviteMemberData $data): ProjectInvitation
    {
        $email = mb_strtolower(trim($data->email));

        if ($project->members()->whereHas('user', fn ($query) => $query->whereRaw('lower(email) = ?', [$email]))->exists()) {
            throw ValidationException::withMessages([
                'email' => 'Cette personne est déjà membre du projet.',
            ]);
        }

        $existing = $this->invitations->findForProjectAndEmail($project->getKey(), $email);

        if ($existing && $existing->isPending()) {
            throw ValidationException::withMessages([
                'email' => 'Une invitation est déjà en attente pour cette adresse.',
            ]);
        }

        $plainToken = Str::random(40);
        $attributes = [
            'project_id' => $project->getKey(),
            'email' => $email,
            'role' => $data->role,
            'token' => hash('sha256', $plainToken),
            'expires_at' => now()->addDays((int) config('roboforge.invitations.expires_after_days')),
            'accepted_at' => null,
            'invited_by' => $actor->getKey(),
        ];

        $invitation = $existing
            ? $this->invitations->updateAttributes($existing, $attributes)
            : $this->invitations->create($attributes);

        Notification::route('mail', $email)->notify(new ProjectInvitationNotification($invitation, $plainToken));

        $activity = $this->activities->create([
            'project_id' => $project->getKey(),
            'actor_id' => $actor->getKey(),
            'event' => 'member.invited',
            'subject_type' => ProjectInvitation::class,
            'subject_id' => $invitation->getKey(),
            'properties' => ['email' => $email, 'role' => $data->role->value],
        ]);

        $this->notifications->notifyProjectEvent($activity);

        return $invitation;
    }

    public function findByPlainToken(string $plainToken): ?ProjectInvitation
    {
        return $this->invitations->findByHashedToken(hash('sha256', $plainToken));
    }

    public function accept(ProjectInvitation $invitation, User $user): ProjectMember
    {
        $activity = null;

        $member = DB::transaction(function () use ($invitation, $user, &$activity): ProjectMember {
            $member = $this->memberships->create([
                'project_id' => $invitation->project_id,
                'user_id' => $user->getKey(),
                'role' => $invitation->role,
                'joined_at' => now(),
            ]);

            $this->invitations->markAccepted($invitation);

            $activity = $this->activities->create([
                'project_id' => $invitation->project_id,
                'actor_id' => $user->getKey(),
                'event' => 'member.joined',
                'subject_type' => ProjectMember::class,
                'subject_id' => $member->getKey(),
                'properties' => ['email' => $invitation->email, 'role' => $invitation->role->value],
            ]);

            return $member;
        });

        $this->notifications->notifyProjectEvent($activity);

        return $member;
    }
}
