<?php

namespace App\Http\Controllers;

use App\Domain\Projects\Contracts\ProjectInvitationRepositoryInterface;
use App\Domain\Projects\Contracts\ProjectMembershipRepositoryInterface;
use App\Domain\Projects\DTOs\InviteMemberData;
use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Domain\Projects\Services\ProjectInvitationService;
use App\Domain\Projects\Services\ProjectMembershipService;
use App\Http\Requests\StoreInvitationRequest;
use App\Http\Requests\UpdateMemberRoleRequest;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectMemberController extends Controller
{
    public function __construct(
        private readonly ProjectMembershipRepositoryInterface $memberships,
        private readonly ProjectInvitationRepositoryInterface $invitations,
        private readonly ProjectMembershipService $membershipService,
        private readonly ProjectInvitationService $invitationService,
    ) {}

    public function index(Project $project): Response
    {
        $this->authorize('view', $project);

        return Inertia::render('Projects/Members', [
            'project' => ['id' => $project->id, 'name' => $project->name],
            'members' => $this->memberships->forProject($project->getKey())
                ->map(fn (ProjectMember $member): array => $this->memberPayload($project, $member))
                ->values(),
            'invitations' => $this->invitations->pendingForProject($project->getKey())
                ->map(fn (ProjectInvitation $invitation): array => [
                    'id' => $invitation->getKey(),
                    'email' => $invitation->email,
                    'role' => $invitation->role->value,
                    'expires_at' => $invitation->expires_at->toDateTimeString(),
                    'created_at' => $invitation->created_at->toDateTimeString(),
                ])
                ->values(),
            'roles' => $this->assignableRoles(),
            'canManage' => request()->user()->can('inviteMembers', $project),
        ]);
    }

    public function storeInvitation(StoreInvitationRequest $request, Project $project): RedirectResponse
    {
        $validated = $request->validated();

        $this->invitationService->invite($project, $request->user(), new InviteMemberData(
            email: $validated['email'],
            role: ProjectMemberRole::from($validated['role']),
        ));

        return back()->with('success', 'Invitation envoyée.');
    }

    public function update(UpdateMemberRoleRequest $request, Project $project, ProjectMember $member): RedirectResponse
    {
        $this->membershipService->updateRole(
            $project,
            $member,
            $request->user(),
            ProjectMemberRole::from($request->validated('role')),
        );

        return back()->with('success', 'Rôle mis à jour.');
    }

    public function destroy(Request $request, Project $project, ProjectMember $member): RedirectResponse
    {
        $this->authorize('removeMember', [$project, $member]);

        $this->membershipService->remove($project, $member, $request->user());

        return to_route('projects.members.index', $project)->with('success', 'Membre retiré du projet.');
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

                    return to_route('projects.show', $invitation->project)
                        ->with('success', 'Vous avez rejoint le projet.');
                }

                $status = 'email_mismatch';
            } else {
                redirect()->setIntendedUrl($request->fullUrl());
                $status = 'guest';
            }
        }

        return Inertia::render('Invitations/Accept', [
            'status' => $status,
            'project' => $invitation ? ['name' => $invitation->project->name] : null,
            'role' => $invitation?->role->value,
            'email' => $invitation?->email,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function memberPayload(Project $project, ProjectMember $member): array
    {
        return [
            'id' => $member->getKey(),
            'role' => $member->role->value,
            'is_owner' => $member->user_id === $project->owner_id,
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
        return collect(ProjectMemberRole::cases())
            ->reject(fn (ProjectMemberRole $role): bool => $role === ProjectMemberRole::Owner)
            ->map(fn (ProjectMemberRole $role): string => $role->value)
            ->values()
            ->all();
    }
}
