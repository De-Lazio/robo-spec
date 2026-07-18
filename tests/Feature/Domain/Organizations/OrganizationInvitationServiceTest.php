<?php

namespace Tests\Feature\Domain\Organizations;

use App\Domain\Organizations\DTOs\InviteOrganizationMemberData;
use App\Domain\Organizations\Enums\OrganizationRole;
use App\Domain\Organizations\Services\OrganizationInvitationService;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Notifications\OrganizationInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrganizationInvitationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_invitation_and_notifies_the_invitee(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->getKey()]);

        $invitation = app(OrganizationInvitationService::class)->invite($organization, $owner, new InviteOrganizationMemberData(
            email: 'Invitee@Example.com',
            role: OrganizationRole::Member,
        ));

        $this->assertSame('invitee@example.com', $invitation->email);
        $this->assertNotEmpty($invitation->token);

        Notification::assertSentOnDemand(
            OrganizationInvitationNotification::class,
            fn (OrganizationInvitationNotification $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notifiable->routes['mail'] === 'invitee@example.com',
        );
    }

    public function test_it_rejects_a_duplicate_pending_invitation(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(OrganizationInvitationService::class);

        $service->invite($organization, $owner, new InviteOrganizationMemberData('invitee@example.com', OrganizationRole::Member));

        $this->expectException(ValidationException::class);
        $service->invite($organization, $owner, new InviteOrganizationMemberData('invitee@example.com', OrganizationRole::Admin));
    }

    public function test_it_rejects_inviting_an_existing_member(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $member = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->getKey()]);
        OrganizationMember::query()->create([
            'organization_id' => $organization->getKey(),
            'user_id' => $member->getKey(),
            'role' => OrganizationRole::Member,
            'joined_at' => now(),
        ]);

        $this->expectException(ValidationException::class);
        app(OrganizationInvitationService::class)->invite($organization, $owner, new InviteOrganizationMemberData($member->email, OrganizationRole::Admin));
    }

    public function test_it_accepts_an_invitation_and_creates_a_membership(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->getKey()]);
        $invitee = User::factory()->create(['email' => 'invitee@example.com']);
        $service = app(OrganizationInvitationService::class);

        $invitation = $service->invite($organization, $owner, new InviteOrganizationMemberData('invitee@example.com', OrganizationRole::Admin));

        $member = $service->accept($invitation, $invitee);

        $this->assertSame(OrganizationRole::Admin, $member->role);
        $this->assertTrue($invitation->fresh()->isAccepted());
    }
}
