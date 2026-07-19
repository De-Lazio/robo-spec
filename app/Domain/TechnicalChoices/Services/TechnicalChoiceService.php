<?php

namespace App\Domain\TechnicalChoices\Services;

use App\Domain\Notifications\Services\NotificationService;
use App\Domain\Projects\Contracts\ProjectActivityRepositoryInterface;
use App\Domain\TechnicalChoices\Contracts\ProjectComponentRepositoryInterface;
use App\Domain\TechnicalChoices\DTOs\ProjectComponentData;
use App\Models\Component;
use App\Models\Project;
use App\Models\ProjectComponent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TechnicalChoiceService
{
    public function __construct(
        private readonly ProjectComponentRepositoryInterface $choices,
        private readonly ProjectActivityRepositoryInterface $activities,
        private readonly NotificationService $notifications,
    ) {}

    public function addComponent(Project $project, User $actor, ProjectComponentData $data): ProjectComponent
    {
        $component = Component::query()->findOrFail($data->componentId);

        if (! $component->is_active) {
            throw ValidationException::withMessages([
                'component_id' => ['Ce composant a été retiré du catalogue et ne peut plus être ajouté.'],
            ]);
        }

        $activity = null;

        $choice = DB::transaction(function () use ($project, $actor, $data, $component, &$activity): ProjectComponent {
            $choice = $this->choices->create([
                'project_id' => $project->getKey(),
                'component_id' => $data->componentId,
                'quantity' => $data->quantity,
                'rationale' => $data->rationale,
                'linked_function_ids' => $data->linkedFunctionIds,
                'added_by' => $actor->getKey(),
            ]);

            $activity = $this->activities->create([
                'project_id' => $project->getKey(),
                'actor_id' => $actor->getKey(),
                'event' => 'technical_choice.added',
                'subject_type' => ProjectComponent::class,
                'subject_id' => $choice->getKey(),
                'properties' => ['component' => $component->name],
            ]);

            return $choice;
        });

        $this->notifications->notifyProjectEvent($activity);

        return $choice;
    }

    public function updateComponent(ProjectComponent $choice, ProjectComponentData $data): ProjectComponent
    {
        return $this->choices->update($choice, [
            'quantity' => $data->quantity,
            'rationale' => $data->rationale,
            'linked_function_ids' => $data->linkedFunctionIds,
        ]);
    }

    public function removeComponent(Project $project, ProjectComponent $choice, User $actor): void
    {
        $activity = null;

        DB::transaction(function () use ($project, $choice, $actor, &$activity): void {
            $activity = $this->activities->create([
                'project_id' => $project->getKey(),
                'actor_id' => $actor->getKey(),
                'event' => 'technical_choice.removed',
                'subject_type' => ProjectComponent::class,
                'subject_id' => $choice->getKey(),
                'properties' => ['component' => $choice->component?->name],
            ]);

            $this->choices->delete($choice);
        });

        $this->notifications->notifyProjectEvent($activity);
    }
}
