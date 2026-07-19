<?php

namespace Tests\Feature\Projects;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Models\Component;
use App\Models\ComponentCategory;
use App\Models\Project;
use App\Models\ProjectComponent;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectComponentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_contributor_can_create_edit_and_delete_a_local_component(): void
    {
        $owner = User::factory()->create();
        $contributor = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $this->createMember($project, $contributor, ProjectMemberRole::Contributor);
        $category = ComponentCategory::factory()->create();

        $this->actingAs($contributor)->post(route('projects.local-components.store', $project), [
            'component_category_id' => $category->getKey(),
            'name' => 'Support moteur imprimé',
            'price_cents' => 250,
        ])->assertRedirect();

        $component = Component::query()->where('owner_project_id', $project->getKey())->firstOrFail();
        $this->assertSame('Support moteur imprimé', $component->name);
        $this->assertSame($project->getKey(), $component->owner_project_id);

        $this->actingAs($contributor)->put(route('projects.local-components.update', [$project, $component]), [
            'component_category_id' => $category->getKey(),
            'name' => 'Support moteur v2',
        ])->assertRedirect();

        $this->assertSame('Support moteur v2', $component->fresh()->name);

        $this->actingAs($contributor)->delete(route('projects.local-components.destroy', [$project, $component]))->assertRedirect();
        $this->assertDatabaseMissing('components', ['id' => $component->getKey()]);
    }

    public function test_an_outsider_cannot_create_edit_or_delete_a_local_component(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $category = ComponentCategory::factory()->create();
        $component = Component::factory()->create(['created_by' => $owner->getKey(), 'owner_project_id' => $project->getKey()]);
        $outsider = User::factory()->create();

        $this->actingAs($outsider)->post(route('projects.local-components.store', $project), [
            'component_category_id' => $category->getKey(),
            'name' => 'Hack',
        ])->assertForbidden();

        $this->actingAs($outsider)->put(route('projects.local-components.update', [$project, $component]), [
            'component_category_id' => $category->getKey(),
            'name' => 'Hack',
        ])->assertForbidden();

        $this->actingAs($outsider)->delete(route('projects.local-components.destroy', [$project, $component]))->assertForbidden();
    }

    public function test_a_local_component_from_another_project_is_not_reachable_through_the_scoped_route(): void
    {
        $owner = User::factory()->create();
        $projectA = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $projectB = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $category = ComponentCategory::factory()->create();
        $componentOfB = Component::factory()->create(['created_by' => $owner->getKey(), 'owner_project_id' => $projectB->getKey()]);

        $this->actingAs($owner)->put(route('projects.local-components.update', [$projectA, $componentOfB]), [
            'component_category_id' => $category->getKey(),
            'name' => 'Hack',
        ])->assertNotFound();

        $this->actingAs($owner)->delete(route('projects.local-components.destroy', [$projectA, $componentOfB]))->assertNotFound();
    }

    public function test_deleting_a_local_component_still_used_as_a_technical_choice_is_rejected(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $component = Component::factory()->create(['created_by' => $owner->getKey(), 'owner_project_id' => $project->getKey()]);
        ProjectComponent::query()->create([
            'project_id' => $project->getKey(),
            'component_id' => $component->getKey(),
            'quantity' => 1,
            'added_by' => $owner->getKey(),
        ]);

        $this->actingAs($owner)
            ->delete(route('projects.local-components.destroy', [$project, $component]))
            ->assertSessionHasErrors('component');

        $this->assertDatabaseHas('components', ['id' => $component->getKey()]);
    }

    private function createMember(Project $project, User $user, ProjectMemberRole $role): ProjectMember
    {
        return ProjectMember::query()->create([
            'project_id' => $project->getKey(),
            'user_id' => $user->getKey(),
            'role' => $role,
            'joined_at' => now(),
        ]);
    }
}
