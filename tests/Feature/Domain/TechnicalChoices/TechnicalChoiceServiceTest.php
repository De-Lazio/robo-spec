<?php

namespace Tests\Feature\Domain\TechnicalChoices;

use App\Domain\TechnicalChoices\DTOs\ProjectComponentData;
use App\Domain\TechnicalChoices\Services\TechnicalChoiceService;
use App\Models\Component;
use App\Models\Project;
use App\Models\ProjectComponent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TechnicalChoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_component_creates_the_choice_and_logs_an_activity(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $component = Component::factory()->create(['is_active' => true]);
        $service = app(TechnicalChoiceService::class);

        $choice = $service->addComponent($project, $owner, new ProjectComponentData(
            componentId: $component->getKey(),
            quantity: 2,
            rationale: 'Deux moteurs pour la traction',
            linkedFunctionIds: ['F1'],
        ));

        $this->assertSame(2, $choice->quantity);
        $this->assertSame(['F1'], $choice->linked_function_ids);
        $this->assertDatabaseHas('project_components', [
            'project_id' => $project->getKey(),
            'component_id' => $component->getKey(),
            'added_by' => $owner->getKey(),
        ]);
        $this->assertDatabaseHas('project_activities', [
            'project_id' => $project->getKey(),
            'event' => 'technical_choice.added',
        ]);
    }

    public function test_add_component_rejects_an_inactive_component(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $component = Component::factory()->create(['is_active' => false]);
        $service = app(TechnicalChoiceService::class);

        $this->expectException(ValidationException::class);

        $service->addComponent($project, $owner, new ProjectComponentData(
            componentId: $component->getKey(),
            quantity: 1,
            rationale: null,
            linkedFunctionIds: [],
        ));
    }

    public function test_update_component_changes_quantity_rationale_and_linked_functions(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $component = Component::factory()->create(['is_active' => true]);
        $service = app(TechnicalChoiceService::class);

        $choice = $service->addComponent($project, $owner, new ProjectComponentData(
            componentId: $component->getKey(),
            quantity: 1,
            rationale: null,
            linkedFunctionIds: [],
        ));

        $updated = $service->updateComponent($choice, new ProjectComponentData(
            componentId: $component->getKey(),
            quantity: 5,
            rationale: 'Mis à jour',
            linkedFunctionIds: ['F2'],
        ));

        $this->assertSame(5, $updated->quantity);
        $this->assertSame('Mis à jour', $updated->rationale);
        $this->assertSame(['F2'], $updated->linked_function_ids);
    }

    public function test_remove_component_deletes_the_choice_and_logs_an_activity(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $component = Component::factory()->create(['is_active' => true]);
        $service = app(TechnicalChoiceService::class);

        $choice = $service->addComponent($project, $owner, new ProjectComponentData(
            componentId: $component->getKey(),
            quantity: 1,
            rationale: null,
            linkedFunctionIds: [],
        ));

        $service->removeComponent($project, $choice, $owner);

        $this->assertDatabaseMissing('project_components', ['id' => $choice->getKey()]);
        $this->assertDatabaseHas('project_activities', [
            'project_id' => $project->getKey(),
            'event' => 'technical_choice.removed',
        ]);
    }

    public function test_update_component_does_not_require_the_component_to_still_be_active(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $component = Component::factory()->create(['is_active' => true]);
        $service = app(TechnicalChoiceService::class);

        $choice = $service->addComponent($project, $owner, new ProjectComponentData(
            componentId: $component->getKey(),
            quantity: 1,
            rationale: null,
            linkedFunctionIds: [],
        ));

        $component->update(['is_active' => false]);

        $updated = $service->updateComponent($choice, new ProjectComponentData(
            componentId: $component->getKey(),
            quantity: 3,
            rationale: null,
            linkedFunctionIds: [],
        ));

        $this->assertSame(3, $updated->quantity);
        $this->assertInstanceOf(ProjectComponent::class, $updated);
    }
}
