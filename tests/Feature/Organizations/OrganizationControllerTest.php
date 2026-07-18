<?php

namespace Tests\Feature\Organizations;

use App\Domain\Organizations\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OrganizationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_can_create_an_organization(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->post(route('organizations.store'), [
            'name' => 'Robotics Club',
            'description' => 'Une super équipe',
        ]);

        $organization = Organization::query()->firstOrFail();

        $response->assertRedirect(route('organizations.show', $organization));
        $this->assertDatabaseHas('organizations', ['id' => $organization->getKey(), 'owner_id' => $owner->getKey()]);
        $this->assertDatabaseCount('organization_members', 1);
    }

    public function test_organization_creation_requires_a_name(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('organizations.store'), ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_an_outsider_cannot_view_an_organization(): void
    {
        $organization = Organization::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('organizations.show', $organization))
            ->assertForbidden();
    }

    public function test_a_member_can_view_the_organization_and_its_projects(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->getKey()]);
        Project::factory()->create(['owner_id' => $owner->getKey(), 'organization_id' => $organization->getKey()]);

        $this->actingAs($owner)
            ->get(route('organizations.show', $organization))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Organizations/Show')
                ->where('organization.name', $organization->name)
                ->has('projects', 1));
    }

    public function test_an_outsider_cannot_update_or_delete_an_organization(): void
    {
        $organization = Organization::factory()->create();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->put(route('organizations.update', $organization), ['name' => 'Hack'])
            ->assertForbidden();

        $this->actingAs($outsider)
            ->delete(route('organizations.destroy', $organization))
            ->assertForbidden();
    }

    public function test_an_admin_can_update_but_only_the_owner_can_delete(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->getKey()]);
        OrganizationMember::query()->create([
            'organization_id' => $organization->getKey(),
            'user_id' => $admin->getKey(),
            'role' => OrganizationRole::Admin,
            'joined_at' => now(),
        ]);

        $this->actingAs($admin)
            ->put(route('organizations.update', $organization), ['name' => 'Nouveau nom'])
            ->assertRedirect(route('organizations.settings', $organization));
        $this->assertDatabaseHas('organizations', ['id' => $organization->getKey(), 'name' => 'Nouveau nom']);

        $this->actingAs($admin)
            ->delete(route('organizations.destroy', $organization))
            ->assertForbidden();

        $this->actingAs($owner)
            ->delete(route('organizations.destroy', $organization))
            ->assertRedirect(route('organizations.index'));
        $this->assertSoftDeleted('organizations', ['id' => $organization->getKey()]);
    }
}
