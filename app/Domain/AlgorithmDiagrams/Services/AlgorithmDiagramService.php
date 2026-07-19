<?php

namespace App\Domain\AlgorithmDiagrams\Services;

use App\Domain\AlgorithmDiagrams\Contracts\AlgorithmDiagramRepositoryInterface;
use App\Domain\AlgorithmDiagrams\DTOs\AlgorithmDiagramData;
use App\Domain\AlgorithmDiagrams\Enums\DiagramFormalism;
use App\Domain\AlgorithmDiagrams\Rules\NodeComponentRules;
use App\Domain\Components\Enums\ComponentType;
use App\Domain\Notifications\Services\NotificationService;
use App\Domain\Projects\Contracts\ProjectActivityRepositoryInterface;
use App\Models\AlgorithmDiagram;
use App\Models\Project;
use App\Models\ProjectComponent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AlgorithmDiagramService
{
    public function __construct(
        private readonly AlgorithmDiagramRepositoryInterface $diagrams,
        private readonly ProjectActivityRepositoryInterface $activities,
        private readonly NotificationService $notifications,
    ) {}

    public function create(Project $project, User $actor, string $name, DiagramFormalism $formalism): AlgorithmDiagram
    {
        $activity = null;

        $diagram = DB::transaction(function () use ($project, $actor, $name, $formalism, &$activity): AlgorithmDiagram {
            $diagram = $this->diagrams->create([
                'project_id' => $project->getKey(),
                'name' => $name,
                'formalism' => $formalism,
                'data' => ['nodes' => [], 'edges' => [], 'variables' => []],
                'created_by' => $actor->getKey(),
            ]);

            $activity = $this->activities->create([
                'project_id' => $project->getKey(),
                'actor_id' => $actor->getKey(),
                'event' => 'algorithm_diagram.created',
                'subject_type' => AlgorithmDiagram::class,
                'subject_id' => $diagram->getKey(),
                'properties' => ['name' => $diagram->name],
            ]);

            return $diagram;
        });

        $this->notifications->notifyProjectEvent($activity);

        return $diagram;
    }

    public function save(AlgorithmDiagram $diagram, User $actor, AlgorithmDiagramData $data): AlgorithmDiagram
    {
        $errors = $this->validateComponentAssociations($diagram->project_id, $data);

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        $activity = null;

        $diagram = DB::transaction(function () use ($diagram, $actor, $data, &$activity): AlgorithmDiagram {
            $diagram = $this->diagrams->update($diagram, [
                'name' => $data->name,
                'data' => $data->data,
                'microcontroller_component_id' => $data->microcontrollerComponentId,
                'energy_source_component_id' => $data->energySourceComponentId,
                'updated_by' => $actor->getKey(),
            ]);

            $activity = $this->activities->create([
                'project_id' => $diagram->project_id,
                'actor_id' => $actor->getKey(),
                'event' => 'algorithm_diagram.updated',
                'subject_type' => AlgorithmDiagram::class,
                'subject_id' => $diagram->getKey(),
                'properties' => ['name' => $diagram->name],
            ]);

            return $diagram;
        });

        $this->notifications->notifyProjectEvent($activity);

        return $diagram;
    }

    public function delete(Project $project, AlgorithmDiagram $diagram, User $actor): void
    {
        $activity = null;

        DB::transaction(function () use ($project, $diagram, $actor, &$activity): void {
            $activity = $this->activities->create([
                'project_id' => $project->getKey(),
                'actor_id' => $actor->getKey(),
                'event' => 'algorithm_diagram.deleted',
                'subject_type' => AlgorithmDiagram::class,
                'subject_id' => $diagram->getKey(),
                'properties' => ['name' => $diagram->name],
            ]);

            $this->diagrams->delete($diagram);
        });

        $this->notifications->notifyProjectEvent($activity);
    }

    /**
     * @return array<string, list<string>>
     */
    private function validateComponentAssociations(string $projectId, AlgorithmDiagramData $data): array
    {
        $errors = [];
        $nodes = $data->data['nodes'] ?? [];

        foreach ($nodes as $index => $node) {
            $nodeType = $node['type'] ?? null;
            $componentIds = $node['data']['componentIds'] ?? [];

            if ($componentIds === []) {
                continue;
            }

            $allowed = NodeComponentRules::allowedComponentTypes((string) $nodeType);

            if ($allowed === []) {
                $errors["nodes.{$index}.componentIds"] = ["Le nœud « {$nodeType} » ne peut pas être lié à un composant."];

                continue;
            }

            foreach ($componentIds as $componentId) {
                $error = $this->validateComponentReference($projectId, $componentId, $allowed);

                if ($error !== null) {
                    $errors["nodes.{$index}.componentIds"][] = $error;
                }
            }
        }

        foreach ([
            'microcontrollerComponentId' => ComponentType::Microcontroller,
            'energySourceComponentId' => ComponentType::EnergySource,
        ] as $property => $expectedType) {
            $componentId = $data->{$property};

            if ($componentId === null) {
                continue;
            }

            $error = $this->validateComponentReference($projectId, $componentId, [$expectedType]);

            if ($error !== null) {
                $errors[$property] = [$error];
            }
        }

        return $errors;
    }

    /**
     * @param  list<ComponentType>|null  $allowedTypes  null means any type is allowed
     */
    private function validateComponentReference(string $projectId, mixed $componentId, ?array $allowedTypes): ?string
    {
        $choice = ProjectComponent::query()->with('component.category')->find($componentId);

        if (! $choice || $choice->project_id !== $projectId) {
            return "Le composant sélectionné n'appartient pas à ce projet.";
        }

        if ($allowedTypes !== null && ! in_array($choice->component->category->type, $allowedTypes, true)) {
            return "Le composant « {$choice->component->name} » n'est pas d'un type autorisé ici.";
        }

        return null;
    }
}
