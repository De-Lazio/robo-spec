<?php

namespace App\Http\Controllers;

use App\Domain\Organizations\Contracts\OrganizationInvitationRepositoryInterface;
use App\Domain\Organizations\Contracts\OrganizationMembershipRepositoryInterface;
use App\Domain\Organizations\DTOs\InviteOrganizationMemberData;
use App\Domain\Organizations\Enums\OrganizationRole;
use App\Domain\Organizations\Services\OrganizationInvitationService;
use App\Domain\Organizations\Services\OrganizationMembershipService;
use App\Http\Requests\StoreOrganizationInvitationRequest;
use App\Http\Requests\UpdateOrganizationMemberRoleRequest;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationMemberController extends Controller
{
    public function __construct(
        private readonly OrganizationMembershipRepositoryInterface $memberships,
        private readonly OrganizationInvitationRepositoryInterface $invitations,
        private readonly OrganizationMembershipService $membershipService,
        private readonly OrganizationInvitationService $invitationService,
    ) {}

    public function index(Organization $organization): Response
    {
        $this->authorize('view', $organization);

        return Inertia::render('Organizations/Members', [
            'organization' => ['id' => $organization->id, 'name' => $organization->name],
            'members' => $this->memberships->forOrganization($organization->getKey())
                ->map(fn (OrganizationMember $member): array => $this->memberPayload($organization, $member))
                ->values(),
            'invitations' => $this->invitations->pendingForOrganization($organization->getKey())
                ->map(fn (OrganizationInvitation $invitation): array => [
                    'id' => $invitation->getKey(),
                    'email' => $invitation->email,
                    'role' => $invitation->role->value,
                    'expires_at' => $invitation->expires_at->toDateTimeString(),
                    'created_at' => $invitation->created_at->toDateTimeString(),
                ])
                ->values(),
            'roles' => $this->assignableRoles(),
            'canManage' => request()->user()->can('inviteMembers', $organization),
        ]);
    }

    public function storeInvitation(StoreOrganizationInvitationRequest $request, Organization $organization): RedirectResponse
    {
        $validated = $request->validated();

        $this->invitationService->invite($organization, $request->user(), new InviteOrganizationMemberData(
            email: $validated['email'],
            role: OrganizationRole::from($validated['role']),
        ));

        return back()->with('success', 'Invitation envoyée.');
    }

    public function update(UpdateOrganizationMemberRoleRequest $request, Organization $organization, OrganizationMember $member): RedirectResponse
    {
        $this->membershipService->updateRole(
            $organization,
            $member,
            OrganizationRole::from($request->validated('role')),
        );

        return back()->with('success', 'Rôle mis à jour.');
    }

    public function destroy(Request $request, Organization $organization, OrganizationMember $member): RedirectResponse
    {
        $this->authorize('removeMember', [$organization, $member]);

        $this->membershipService->remove($organization, $member);

        return to_route('organizations.members.index', $organization)->with('success', 'Membre retiré de l’organisation.');
    }

    public function acceptInvitation(Request $request, string $token): Response|RedirectResponse
    {
        $invitation = $this->invitationService->findByPlainToken($token);

        $status = match (true) {
            $invitation === null => 'invalid',
            $invitation->isAccepted() => 'accepted',
            $invitation->isExpired() => 'expired',
            default => null,
        };

        if ($status === null) {
            $user = $request->user();

            if ($user instanceof User) {
                if (mb_strtolower($user->email) === mb_strtolower($invitation->email)) {
                    $this->invitationService->accept($invitation, $user);

                    return to_route('organizations.show', $invitation->organization)
                        ->with('success', 'Vous avez rejoint l’organisation.');
                }

                $status = 'email_mismatch';
            } else {
                redirect()->setIntendedUrl($request->fullUrl());
                $status = 'guest';
            }
        }

        return Inertia::render('Organizations/Invitations/Accept', [
            'status' => $status,
            'organization' => $invitation ? ['name' => $invitation->organization->name] : null,
            'role' => $invitation?->role->value,
            'email' => $invitation?->email,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function memberPayload(Organization $organization, OrganizationMember $member): array
    {
        return [
            'id' => $member->getKey(),
            'role' => $member->role->value,
            'is_owner' => $member->user_id === $organization->owner_id,
            'joined_at' => $member->joined_at?->toDateTimeString(),
            'user' => [
                'name' => $member->user->name,
                'email' => $member->user->email,
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private function assignableRoles(): array
    {
        return collect(OrganizationRole::cases())
            ->reject(fn (OrganizationRole $role): bool => $role === OrganizationRole::Owner)
            ->map(fn (OrganizationRole $role): string => $role->value)
            ->values()
            ->all();
    }
}
