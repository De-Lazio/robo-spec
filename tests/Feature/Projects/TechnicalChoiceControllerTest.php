<?php

namespace Tests\Feature\Projects;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Models\Component;
use App\Models\Project;
use App\Models\ProjectComponent;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TechnicalChoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_outsider_cannot_view_or_manage_technical_choices(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $component = Component::factory()->create(['is_active' => true]);
        $outsider = User::factory()->create();

        $this->actingAs($outsider)->get(route('projects.technical-choices.index', $project))->assertForbidden();
        $this->actingAs($outsider)->post(route('projects.technical-choices.store', $project), [
            'component_id' => $component->getKey(),
            'quantity' => 1,
        ])->assertForbidden();
    }

    public function test_a_contributor_can_add_and_remove_a_technical_choice(): void
    {
        $owner = User::factory()->create();
        $contributor = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $this->createMember($project, $contributor, ProjectMemberRole::Contributor);
        $component = Component::factory()->create(['is_active' => true]);

        $this->actingAs($contributor)->post(route('projects.technical-choices.store', $project), [
            'component_id' => $component->getKey(),
            'quantity' => 3,
            'rationale' => 'Choix moteur',
            'linked_function_ids' => ['F1'],
        ])->assertRedirect();

        $this->assertDatabaseHas('project_components', [
            'project_id' => $project->getKey(),
            'component_id' => $component->getKey(),
            'quantity' => 3,
        ]);

        $choice = ProjectComponent::query()->where('project_id', $project->getKey())->firstOrFail();

        $this->actingAs($contributor)
            ->delete(route('projects.technical-choices.destroy', [$project, $choice]))
            ->assertRedirect();

        $this->assertDatabaseMissing('project_components', ['id' => $choice->getKey()]);
    }

    public function test_a_viewer_cannot_add_a_technical_choice(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $this->createMember($project, $viewer, ProjectMemberRole::Viewer);
        $component = Component::factory()->create(['is_active' => true]);

        $this->actingAs($viewer)->post(route('projects.technical-choices.store', $project), [
            'component_id' => $component->getKey(),
            'quantity' => 1,
        ])->assertForbidden();
    }

    public function test_the_index_reports_the_correct_aggregate_cost_total(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $componentA = Component::factory()->create(['is_active' => true, 'price_cents' => 1000]);
        $componentB = Component::factory()->create(['is_active' => true, 'price_cents' => 2500]);

        ProjectComponent::query()->create([
            'project_id' => $project->getKey(),
            'component_id' => $componentA->getKey(),
            'quantity' => 2,
            'added_by' => $owner->getKey(),
        ]);
        ProjectComponent::query()->create([
            'project_id' => $project->getKey(),
            'component_id' => $componentB->getKey(),
            'quantity' => 1,
            'added_by' => $owner->getKey(),
        ]);

        $this->actingAs($owner)
            ->get(route('projects.technical-choices.index', $project))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Projects/TechnicalChoices/Index')
                ->where('totalCostCents', 4500)
            );
    }

    public function test_a_technical_choice_survives_its_component_being_deactivated_but_a_new_one_is_rejected(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $component = Component::factory()->create(['is_active' => true]);

        $this->actingAs($owner)->post(route('projects.technical-choices.store', $project), [
            'component_id' => $component->getKey(),
            'quantity' => 1,
        ])->assertRedirect();

        $component->update(['is_active' => false]);

        $this->assertDatabaseHas('project_components', [
            'project_id' => $project->getKey(),
            'component_id' => $component->getKey(),
        ]);

        $this->actingAs($owner)->post(route('projects.technical-choices.store', $project), [
            'component_id' => $component->getKey(),
            'quantity' => 1,
        ])->assertSessionHasErrors('component_id');
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
