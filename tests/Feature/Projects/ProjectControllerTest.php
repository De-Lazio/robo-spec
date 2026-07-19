<?php

namespace Tests\Feature\Projects;

use App\Domain\Organizations\Enums\OrganizationRole;
use App\Domain\Projects\Enums\ProjectStatus;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_can_view_the_dashboard(): void
    {
        $owner = User::factory()->create();
        Project::factory()->count(2)->create(['owner_id' => $owner->getKey()]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('projects.data', 2)
                ->where('stats.total', 2));
    }

    public function test_an_authenticated_user_can_create_a_project(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->post(route('projects.store'), [
            'name' => 'Drone de cartographie',
            'description' => 'Collecte d’images aériennes.',
            'robot_type' => 'drone',
            'domain' => 'Agriculture',
            'tags' => ['ESP32', 'GPS'],
        ]);

        $project = Project::query()->firstOrFail();

        $response->assertRedirect(route('projects.show', $project));
        $this->assertDatabaseHas('projects', ['id' => $project->getKey(), 'owner_id' => $owner->getKey()]);
        $this->assertDatabaseCount('project_members', 1);
    }

    public function test_project_creation_requires_valid_data(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('projects.store'), ['name' => '', 'robot_type' => 'unknown'])
            ->assertSessionHasErrors(['name', 'robot_type']);
    }

    public function test_an_outsider_cannot_update_a_project(): void
    {
        $project = Project::factory()->create();

        $this->actingAs(User::factory()->create())
            ->put(route('projects.update', $project), [
                'name' => 'Projet non autorisé',
                'robot_type' => 'mobile',
                'status' => 'draft',
            ])
            ->assertForbidden();
    }

    public function test_an_owner_can_archive_and_restore_a_project(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey(), 'status' => ProjectStatus::InProgress]);

        $this->actingAs($owner)->patch(route('projects.archive', $project))->assertRedirect(route('projects.show', $project));
        $this->assertDatabaseHas('projects', ['id' => $project->getKey(), 'status' => ProjectStatus::Archived->value]);

        $this->actingAs($owner)->patch(route('projects.restore', $project))->assertRedirect(route('projects.show', $project));
        $this->assertDatabaseHas('projects', ['id' => $project->getKey(), 'status' => ProjectStatus::Draft->value, 'archived_at' => null]);
    }

    public function test_an_organization_admin_can_create_a_project_under_it(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->getKey()]);

        $response = $this->actingAs($owner)->post(route('projects.store'), [
            'name' => 'Bras robotisé',
            'robot_type' => 'arm',
            'organization_id' => $organization->getKey(),
        ]);

        $project = Project::query()->firstOrFail();
        $response->assertRedirect(route('projects.show', $project));
        $this->assertDatabaseHas('projects', ['id' => $project->getKey(), 'organization_id' => $organization->getKey()]);
    }

    public function test_a_non_admin_organization_member_cannot_create_a_project_under_it(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->getKey()]);
        OrganizationMember::query()->create([
            'organization_id' => $organization->getKey(),
            'user_id' => $member->getKey(),
            'role' => OrganizationRole::Member,
            'joined_at' => now(),
        ]);

        $this->actingAs($member)->post(route('projects.store'), [
            'name' => 'Bras robotisé',
            'robot_type' => 'arm',
            'organization_id' => $organization->getKey(),
        ])->assertForbidden();

        $this->assertDatabaseMissing('projects', ['organization_id' => $organization->getKey()]);
    }
}
