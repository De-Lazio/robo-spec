<?php

namespace Tests\Feature\Domain\AlgorithmDiagrams;

use App\Domain\AlgorithmDiagrams\DTOs\AlgorithmDiagramData;
use App\Domain\AlgorithmDiagrams\Enums\DiagramFormalism;
use App\Domain\AlgorithmDiagrams\Services\AlgorithmDiagramService;
use App\Domain\Components\Enums\ComponentType;
use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Models\Component;
use App\Models\ComponentCategory;
use App\Models\Project;
use App\Models\ProjectComponent;
use App\Models\ProjectMember;
use App\Models\User;
use App\Notifications\ProjectActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AlgorithmDiagramServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_notifies_other_members(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $contributor = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        ProjectMember::query()->create([
            'project_id' => $project->getKey(),
            'user_id' => $contributor->getKey(),
            'role' => ProjectMemberRole::Contributor,
            'joined_at' => now(),
        ]);

        app(AlgorithmDiagramService::class)->create($project, $owner, 'Séquence principale', DiagramFormalism::Algorigramme);

        Notification::assertSentTo($contributor, ProjectActivityNotification::class);
        Notification::assertNotSentTo($owner, ProjectActivityNotification::class);
    }

    public function test_create_stores_an_empty_diagram_and_logs_an_activity(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(AlgorithmDiagramService::class);

        $diagram = $service->create($project, $owner, 'Séquence principale', DiagramFormalism::Algorigramme);

        $this->assertSame('algorigramme', $diagram->formalism->value);
        $this->assertSame(['nodes' => [], 'edges' => [], 'variables' => []], $diagram->data);
        $this->assertDatabaseHas('algorithm_diagrams', ['id' => $diagram->getKey(), 'project_id' => $project->getKey()]);
        $this->assertDatabaseHas('project_activities', ['project_id' => $project->getKey(), 'event' => 'algorithm_diagram.created']);
    }

    public function test_save_accepts_a_component_of_the_correct_type(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(AlgorithmDiagramService::class);
        $diagram = $service->create($project, $owner, 'Séquence', DiagramFormalism::Algorigramme);
        $choice = $this->createChoice($project, ComponentType::Actuator);

        $updated = $service->save($diagram, $owner, new AlgorithmDiagramData(
            name: 'Séquence',
            data: [
                'nodes' => [
                    ['id' => 'n1', 'type' => 'process', 'position' => ['x' => 0, 'y' => 0], 'data' => ['label' => 'Avancer', 'componentIds' => [$choice->getKey()]]],
                ],
                'edges' => [],
                'variables' => [],
            ],
        ));

        $this->assertSame([$choice->getKey()], $updated->data['nodes'][0]['data']['componentIds']);
        $this->assertDatabaseHas('project_activities', ['project_id' => $project->getKey(), 'event' => 'algorithm_diagram.updated']);
    }

    public function test_save_rejects_a_component_of_the_wrong_type_for_the_node(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(AlgorithmDiagramService::class);
        $diagram = $service->create($project, $owner, 'Séquence', DiagramFormalism::Algorigramme);
        $choice = $this->createChoice($project, ComponentType::Actuator);

        $this->expectException(ValidationException::class);

        $service->save($diagram, $owner, new AlgorithmDiagramData(
            name: 'Séquence',
            data: [
                'nodes' => [
                    ['id' => 'n1', 'type' => 'decision', 'position' => ['x' => 0, 'y' => 0], 'data' => ['label' => 'Obstacle ?', 'componentIds' => [$choice->getKey()]]],
                ],
                'edges' => [],
                'variables' => [],
            ],
        ));
    }

    public function test_save_rejects_a_component_that_does_not_belong_to_the_project(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $otherProject = Project::factory()->create();
        $service = app(AlgorithmDiagramService::class);
        $diagram = $service->create($project, $owner, 'Séquence', DiagramFormalism::Algorigramme);
        $foreignChoice = $this->createChoice($otherProject, ComponentType::Sensor);

        $this->expectException(ValidationException::class);

        $service->save($diagram, $owner, new AlgorithmDiagramData(
            name: 'Séquence',
            data: [
                'nodes' => [
                    ['id' => 'n1', 'type' => 'decision', 'position' => ['x' => 0, 'y' => 0], 'data' => ['label' => 'Obstacle ?', 'componentIds' => [$foreignChoice->getKey()]]],
                ],
                'edges' => [],
                'variables' => [],
            ],
        ));
    }

    public function test_wait_and_calc_nodes_reject_any_component(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(AlgorithmDiagramService::class);
        $diagram = $service->create($project, $owner, 'Séquence', DiagramFormalism::Algorigramme);
        $choice = $this->createChoice($project, ComponentType::Actuator);

        $this->expectException(ValidationException::class);

        $service->save($diagram, $owner, new AlgorithmDiagramData(
            name: 'Séquence',
            data: [
                'nodes' => [
                    ['id' => 'n1', 'type' => 'wait', 'position' => ['x' => 0, 'y' => 0], 'data' => ['label' => 'Attente', 'componentIds' => [$choice->getKey()]]],
                ],
                'edges' => [],
                'variables' => [],
            ],
        ));
    }

    public function test_communication_node_accepts_any_component_type(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(AlgorithmDiagramService::class);
        $diagram = $service->create($project, $owner, 'Séquence', DiagramFormalism::Algorigramme);
        $choice = $this->createChoice($project, ComponentType::Sensor);

        $updated = $service->save($diagram, $owner, new AlgorithmDiagramData(
            name: 'Séquence',
            data: [
                'nodes' => [
                    ['id' => 'n1', 'type' => 'communication', 'position' => ['x' => 0, 'y' => 0], 'data' => ['label' => 'Envoi télémétrie', 'componentIds' => [$choice->getKey()]]],
                ],
                'edges' => [],
                'variables' => [],
            ],
        ));

        $this->assertSame([$choice->getKey()], $updated->data['nodes'][0]['data']['componentIds']);
    }

    public function test_create_persists_the_chosen_formalism(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(AlgorithmDiagramService::class);

        $diagram = $service->create($project, $owner, 'GRAFCET principal', DiagramFormalism::Grafcet);

        $this->assertSame('grafcet', $diagram->formalism->value);
    }

    public function test_grafcet_step_accepts_a_component_of_the_correct_type(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(AlgorithmDiagramService::class);
        $diagram = $service->create($project, $owner, 'GRAFCET', DiagramFormalism::Grafcet);
        $choice = $this->createChoice($project, ComponentType::Actuator);

        $updated = $service->save($diagram, $owner, new AlgorithmDiagramData(
            name: 'GRAFCET',
            data: [
                'nodes' => [
                    ['id' => 'n1', 'type' => 'step', 'position' => ['x' => 0, 'y' => 0], 'data' => ['label' => 'X1', 'componentIds' => [$choice->getKey()]]],
                ],
                'edges' => [],
                'variables' => [],
            ],
        ));

        $this->assertSame([$choice->getKey()], $updated->data['nodes'][0]['data']['componentIds']);
    }

    public function test_grafcet_transition_rejects_a_component_of_the_wrong_type(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(AlgorithmDiagramService::class);
        $diagram = $service->create($project, $owner, 'GRAFCET', DiagramFormalism::Grafcet);
        $choice = $this->createChoice($project, ComponentType::Actuator);

        $this->expectException(ValidationException::class);

        $service->save($diagram, $owner, new AlgorithmDiagramData(
            name: 'GRAFCET',
            data: [
                'nodes' => [
                    ['id' => 't1', 'type' => 'transition', 'position' => ['x' => 0, 'y' => 0], 'data' => ['label' => 'obstacle détecté', 'componentIds' => [$choice->getKey()]]],
                ],
                'edges' => [],
                'variables' => [],
            ],
        ));
    }

    public function test_grafcet_transition_accepts_a_sensor(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(AlgorithmDiagramService::class);
        $diagram = $service->create($project, $owner, 'GRAFCET', DiagramFormalism::Grafcet);
        $choice = $this->createChoice($project, ComponentType::Sensor);

        $updated = $service->save($diagram, $owner, new AlgorithmDiagramData(
            name: 'GRAFCET',
            data: [
                'nodes' => [
                    ['id' => 't1', 'type' => 'transition', 'position' => ['x' => 0, 'y' => 0], 'data' => ['label' => 'obstacle détecté', 'duration' => '5s', 'componentIds' => [$choice->getKey()]]],
                ],
                'edges' => [],
                'variables' => [],
            ],
        ));

        $this->assertSame([$choice->getKey()], $updated->data['nodes'][0]['data']['componentIds']);
    }

    public function test_grafcet_divergence_nodes_reject_any_component(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(AlgorithmDiagramService::class);
        $diagram = $service->create($project, $owner, 'GRAFCET', DiagramFormalism::Grafcet);
        $choice = $this->createChoice($project, ComponentType::Actuator);

        $this->expectException(ValidationException::class);

        $service->save($diagram, $owner, new AlgorithmDiagramData(
            name: 'GRAFCET',
            data: [
                'nodes' => [
                    ['id' => 'd1', 'type' => 'and_divergence', 'position' => ['x' => 0, 'y' => 0], 'data' => ['label' => '', 'componentIds' => [$choice->getKey()]]],
                ],
                'edges' => [],
                'variables' => [],
            ],
        ));
    }

    public function test_delete_removes_the_diagram_and_logs_an_activity(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(AlgorithmDiagramService::class);
        $diagram = $service->create($project, $owner, 'Séquence', DiagramFormalism::Algorigramme);

        $service->delete($project, $diagram, $owner);

        $this->assertDatabaseMissing('algorithm_diagrams', ['id' => $diagram->getKey()]);
        $this->assertDatabaseHas('project_activities', ['project_id' => $project->getKey(), 'event' => 'algorithm_diagram.deleted']);
    }

    private function createChoice(Project $project, ComponentType $type): ProjectComponent
    {
        $category = ComponentCategory::factory()->create(['type' => $type]);
        $component = Component::factory()->create(['component_category_id' => $category->getKey()]);

        return ProjectComponent::query()->create([
            'project_id' => $project->getKey(),
            'component_id' => $component->getKey(),
            'quantity' => 1,
            'added_by' => $project->owner_id,
        ]);
    }
}
