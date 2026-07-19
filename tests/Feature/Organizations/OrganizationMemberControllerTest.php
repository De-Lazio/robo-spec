<?php

namespace Tests\Feature\Organizations;

use App\Domain\Organizations\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Notifications\OrganizationInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OrganizationMemberControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_outsider_cannot_access_or_manage_members(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->getKey()]);
        $member = $this->createMember($organization, User::factory()->create(), OrganizationRole::Member);
        $outsider = User::factory()->create();

        $this->actingAs($outsider)->get(route('organizations.members.index', $organization))->assertForbidden();
        $this->actingAs($outsider)->post(route('organizations.members.invitations.store', $organization), [
            'email' => 'someone@example.com',
            'role' => 'member',
        ])->assertForbidden();
        $this->actingAs($outsider)->put(route('organizations.members.update', [$organization, $member]), ['role' => 'admin'])->assertForbidden();
        $this->actingAs($outsider)->delete(route('organizations.members.destroy', [$organization, $member]))->assertForbidden();
    }

    public function test_an_owner_can_invite_and_change_a_members_role(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->getKey()]);
        $member = $this->createMember($organization, User::factory()->create(), OrganizationRole::Member);

        $this->actingAs($owner)->post(route('organizations.members.invitations.store', $organization), [
            'email' => 'invitee@example.com',
            'role' => 'member',
        ])->assertRedirect();

        $this->assertDatabaseHas('organization_invitations', ['organization_id' => $organization->getKey(), 'email' => 'invitee@example.com']);

        $this->actingAs($owner)
            ->get(route('organizations.members.index', $organization))
            ->assertInertia(fn (Assert $page) => $page->component('Organizations/Members')->has('invitations', 1));

        $this->actingAs($owner)->put(route('organizations.members.update', [$organization, $member]), ['role' => 'admin'])->assertRedirect();
        $this->assertDatabaseHas('organization_members', ['id' => $member->getKey(), 'role' => OrganizationRole::Admin->value]);
    }

    public function test_an_admin_cannot_touch_the_owners_membership(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->getKey()]);
        $ownerMember = $this->createMember($organization, $owner, OrganizationRole::Owner);
        $this->createMember($organization, $admin, OrganizationRole::Admin);

        $this->actingAs($admin)->put(route('organizations.members.update', [$organization, $ownerMember]), ['role' => 'member'])->assertForbidden();
        $this->actingAs($admin)->delete(route('organizations.members.destroy', [$organization, $ownerMember]))->assertForbidden();
    }

    public function test_a_full_guest_can_register_and_join_via_an_invitation_link(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->getKey()]);

        $this->actingAs($owner)->post(route('organizations.members.invitations.store', $organization), [
            'email' => 'newcomer@example.com',
            'role' => 'member',
        ])->assertRedirect();

        $capturedToken = null;
        Notification::assertSentOnDemand(
            OrganizationInvitationNotification::class,
            function (OrganizationInvitationNotification $notification) use (&$capturedToken): bool {
                $capturedToken = $notification->plainToken;

                return true;
            },
        );
        $this->assertNotNull($capturedToken);

        $acceptUrl = route('organization-invitations.accept', ['token' => $capturedToken]);

        $this->post(route('logout'));

        $this->get($acceptUrl)->assertInertia(fn (Assert $page) => $page->component('Organizations/Invitations/Accept')->where('status', 'guest'));

        $this->post(route('register'), [
            'name' => 'Newcomer',
            'email' => 'newcomer@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect($acceptUrl);

        $newcomer = User::query()->where('email', 'newcomer@example.com')->firstOrFail();

        $this->get($acceptUrl)->assertRedirect(route('organizations.show', $organization));

        $this->assertDatabaseHas('organization_members', [
            'organization_id' => $organization->getKey(),
            'user_id' => $newcomer->getKey(),
            'role' => OrganizationRole::Member->value,
        ]);
    }

    public function test_email_mismatch_does_not_create_a_membership(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->getKey()]);
        $otherUser = User::factory()->create();

        $this->actingAs($owner)->post(route('organizations.members.invitations.store', $organization), [
            'email' => 'invitee@example.com',
            'role' => 'member',
        ])->assertRedirect();

        $capturedToken = null;
        Notification::assertSentOnDemand(
            OrganizationInvitationNotification::class,
            function (OrganizationInvitationNotification $notification) use (&$capturedToken): bool {
                $capturedToken = $notification->plainToken;

                return true;
            },
        );

        $this->actingAs($otherUser)
            ->get(route('organization-invitations.accept', ['token' => $capturedToken]))
            ->assertInertia(fn (Assert $page) => $page->component('Organizations/Invitations/Accept')->where('status', 'email_mismatch'));

        $this->assertDatabaseMissing('organization_members', ['organization_id' => $organization->getKey(), 'user_id' => $otherUser->getKey()]);
    }

    private function createMember(Organization $organization, User $user, OrganizationRole $role): OrganizationMember
    {
        return OrganizationMember::query()->create([
            'organization_id' => $organization->getKey(),
            'user_id' => $user->getKey(),
            'role' => $role,
            'joined_at' => now(),
        ]);
    }
}
