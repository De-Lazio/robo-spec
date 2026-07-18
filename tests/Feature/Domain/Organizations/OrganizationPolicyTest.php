<?php

namespace Tests\Feature\Domain\Organizations;

use App\Domain\Organizations\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class OrganizationPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_members_can_view_an_organization(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $outsider = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->getKey()]);

        $this->createMember($organization, $member, OrganizationRole::Member);

        $this->assertTrue(Gate::forUser($owner)->allows('view', $organization));
        $this->assertTrue(Gate::forUser($member)->allows('view', $organization));
        $this->assertFalse(Gate::forUser($outsider)->allows('view', $organization));
    }

    public function test_only_owner_and_admin_can_update_or_invite(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $member = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->getKey()]);

        $this->createMember($organization, $admin, OrganizationRole::Admin);
        $this->createMember($organization, $member, OrganizationRole::Member);

        $this->assertTrue(Gate::forUser($owner)->allows('update', $organization));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $organization));
        $this->assertFalse(Gate::forUser($member)->allows('update', $organization));

        $this->assertTrue(Gate::forUser($admin)->allows('inviteMembers', $organization));
        $this->assertFalse(Gate::forUser($member)->allows('inviteMembers', $organization));

        $this->assertTrue(Gate::forUser($admin)->allows('createProjectUnder', $organization));
        $this->assertFalse(Gate::forUser($member)->allows('createProjectUnder', $organization));
    }

    public function test_only_owner_can_delete(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->getKey()]);
        $this->createMember($organization, $admin, OrganizationRole::Admin);

        $this->assertTrue(Gate::forUser($owner)->allows('delete', $organization));
        $this->assertFalse(Gate::forUser($admin)->allows('delete', $organization));
    }

    public function test_the_owner_cannot_be_demoted_or_removed(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->getKey()]);
        $this->createMember($organization, $admin, OrganizationRole::Admin);

        $ownerMember = OrganizationMember::query()->create([
            'organization_id' => $organization->getKey(),
            'user_id' => $owner->getKey(),
            'role' => OrganizationRole::Owner,
            'joined_at' => now(),
        ]);

        $this->assertFalse(Gate::forUser($admin)->allows('updateMemberRole', [$organization, $ownerMember]));
        $this->assertFalse(Gate::forUser($admin)->allows('removeMember', [$organization, $ownerMember]));
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
