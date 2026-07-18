<?php

namespace Tests\Feature\Domain\Organizations;

use App\Domain\Organizations\DTOs\CreateOrganizationData;
use App\Domain\Organizations\Enums\OrganizationRole;
use App\Domain\Organizations\Services\OrganizationService;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_sets_up_the_organization_and_owner_membership(): void
    {
        $owner = User::factory()->create();
        $service = app(OrganizationService::class);

        $organization = $service->create($owner, new CreateOrganizationData(name: 'Robotics Club', description: 'Une super équipe'));

        $this->assertSame('Robotics Club', $organization->name);
        $this->assertNotEmpty($organization->slug);
        $this->assertDatabaseHas('organization_members', [
            'organization_id' => $organization->getKey(),
            'user_id' => $owner->getKey(),
            'role' => OrganizationRole::Owner->value,
        ]);
    }

    public function test_create_generates_a_unique_slug_on_collision(): void
    {
        $owner = User::factory()->create();
        $service = app(OrganizationService::class);

        $first = $service->create($owner, new CreateOrganizationData(name: 'Robotics Club', description: null));
        $second = $service->create($owner, new CreateOrganizationData(name: 'Robotics Club', description: null));

        $this->assertNotSame($first->slug, $second->slug);
    }

    public function test_update_renames_the_organization(): void
    {
        $owner = User::factory()->create();
        $service = app(OrganizationService::class);
        $organization = $service->create($owner, new CreateOrganizationData(name: 'Old Name', description: null));

        $updated = $service->update($organization, 'New Name', 'Nouvelle description');

        $this->assertSame('New Name', $updated->name);
        $this->assertSame('Nouvelle description', $updated->description);
    }

    public function test_delete_removes_the_organization(): void
    {
        $owner = User::factory()->create();
        $service = app(OrganizationService::class);
        $organization = $service->create($owner, new CreateOrganizationData(name: 'To Delete', description: null));

        $service->delete($organization);

        $this->assertSoftDeleted('organizations', ['id' => $organization->getKey()]);
    }

    public function test_delete_detaches_projects_instead_of_leaving_a_dangling_reference(): void
    {
        $owner = User::factory()->create();
        $service = app(OrganizationService::class);
        $organization = $service->create($owner, new CreateOrganizationData(name: 'To Delete', description: null));
        $project = Project::factory()->create(['owner_id' => $owner->getKey(), 'organization_id' => $organization->getKey()]);

        $service->delete($organization);

        $this->assertNull($project->fresh()->organization_id);
    }
}
