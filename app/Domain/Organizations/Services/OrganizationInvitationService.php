<?php

namespace App\Domain\Organizations\Services;

use App\Domain\Organizations\Contracts\OrganizationInvitationRepositoryInterface;
use App\Domain\Organizations\Contracts\OrganizationMembershipRepositoryInterface;
use App\Domain\Organizations\DTOs\InviteOrganizationMemberData;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Notifications\OrganizationInvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrganizationInvitationService
{
    public function __construct(
        private readonly OrganizationInvitationRepositoryInterface $invitations,
        private readonly OrganizationMembershipRepositoryInterface $memberships,
    ) {}

    public function invite(Organization $organization, User $actor, InviteOrganizationMemberData $data): OrganizationInvitation
    {
        $email = mb_strtolower(trim($data->email));

        if ($organization->members()->whereHas('user', fn ($query) => $query->whereRaw('lower(email) = ?', [$email]))->exists()) {
            throw ValidationException::withMessages([
                'email' => 'Cette personne est déjà membre de l’organisation.',
            ]);
        }

        $existing = $this->invitations->findForOrganizationAndEmail($organization->getKey(), $email);

        if ($existing && $existing->isPending()) {
            throw ValidationException::withMessages([
                'email' => 'Une invitation est déjà en attente pour cette adresse.',
            ]);
        }

        $plainToken = Str::random(40);
        $attributes = [
            'organization_id' => $organization->getKey(),
            'email' => $email,
            'role' => $data->role,
            'token' => hash('sha256', $plainToken),
            'expires_at' => now()->addDays((int) config('roboforge.organizations.invitations.expires_after_days')),
            'accepted_at' => null,
            'invited_by' => $actor->getKey(),
        ];

        $invitation = $existing
            ? $this->invitations->updateAttributes($existing, $attributes)
            : $this->invitations->create($attributes);

        Notification::route('mail', $email)->notify(new OrganizationInvitationNotification($invitation, $plainToken));

        return $invitation;
    }

    public function findByPlainToken(string $plainToken): ?OrganizationInvitation
    {
        return $this->invitations->findByHashedToken(hash('sha256', $plainToken));
    }

    public function accept(OrganizationInvitation $invitation, User $user): OrganizationMember
    {
        return DB::transaction(function () use ($invitation, $user): OrganizationMember {
            $member = $this->memberships->create([
                'organization_id' => $invitation->organization_id,
                'user_id' => $user->getKey(),
                'role' => $invitation->role,
                'joined_at' => now(),
            ]);

            $this->invitations->markAccepted($invitation);

            return $member;
        });
    }
}
