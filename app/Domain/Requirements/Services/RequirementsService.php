<?php

namespace App\Domain\Requirements\Services;

use App\Domain\Projects\Contracts\ProjectActivityRepositoryInterface;
use App\Domain\Requirements\Contracts\RequirementsDocumentRepositoryInterface;
use App\Domain\Requirements\Enums\RequirementsStatus;
use App\Domain\Requirements\Rules\RequirementsStepRules;
use App\Domain\Requirements\Support\RequirementsDefaults;
use App\Models\Project;
use App\Models\RequirementsDocument;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RequirementsService
{
    public function __construct(
        private readonly RequirementsDocumentRepositoryInterface $documents,
        private readonly ProjectActivityRepositoryInterface $activities,
    ) {}

    public function currentForDisplay(Project $project): ?RequirementsDocument
    {
        return $this->documents->latestPublishedForProject($project->getKey());
    }

    public function getOrCreateDraft(Project $project, User $actor): RequirementsDocument
    {
        $draft = $this->documents->activeDraftForProject($project->getKey());

        if ($draft) {
            return $draft;
        }

        $published = $this->documents->latestPublishedForProject($project->getKey());

        if ($published) {
            return $this->documents->create([
                'project_id' => $project->getKey(),
                'version' => $published->version + 1,
                'status' => RequirementsStatus::Draft,
                'current_step' => 1,
                'data' => $published->data,
                'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ]);
        }

        $defaults = RequirementsDefaults::make();
        $defaults['step1']['projectName'] = $project->name;
        $defaults['step1']['projectType'] = $project->robot_type->value;

        return $this->documents->create([
            'project_id' => $project->getKey(),
            'version' => 1,
            'status' => RequirementsStatus::Draft,
            'current_step' => 1,
            'data' => $defaults,
            'created_by' => $actor->getKey(),
            'updated_by' => $actor->getKey(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function saveStep(RequirementsDocument $document, int $step, array $data, User $actor): RequirementsDocument
    {
        return DB::transaction(function () use ($document, $step, $data, $actor): RequirementsDocument {
            $fullData = $document->data;
            $fullData['step'.$step] = $data;

            $document = $this->documents->update($document, [
                'data' => $fullData,
                'current_step' => $step,
                'updated_by' => $actor->getKey(),
            ]);

            $this->activities->create([
                'project_id' => $document->project_id,
                'actor_id' => $actor->getKey(),
                'event' => 'requirements.step_saved',
                'subject_type' => RequirementsDocument::class,
                'subject_id' => $document->getKey(),
                'properties' => ['step' => $step],
            ]);

            return $document;
        });
    }

    /**
     * @param  array<string, mixed>  $fullData
     */
    public function saveDraft(RequirementsDocument $document, array $fullData, User $actor): RequirementsDocument
    {
        return $this->documents->update($document, [
            'data' => $fullData,
            'updated_by' => $actor->getKey(),
        ]);
    }

    /**
     * @return array<int, bool>
     */
    public function completedSteps(RequirementsDocument $document): array
    {
        $result = [];

        foreach (range(1, RequirementsStepRules::TOTAL_STEPS) as $step) {
            $validator = Validator::make(
                $document->data['step'.$step] ?? [],
                RequirementsStepRules::rules($step),
            );

            $result[$step] = $validator->passes();
        }

        return $result;
    }

    public function publish(RequirementsDocument $document, User $actor): RequirementsDocument
    {
        $errors = [];

        foreach (range(1, RequirementsStepRules::TOTAL_STEPS) as $step) {
            $validator = Validator::make(
                $document->data['step'.$step] ?? [],
                RequirementsStepRules::rules($step),
            );

            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $field => $messages) {
                    $errors["step{$step}.{$field}"] = $messages;
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return DB::transaction(function () use ($document, $actor): RequirementsDocument {
            $document = $this->documents->update($document, [
                'status' => RequirementsStatus::Published,
                'published_at' => now(),
                'updated_by' => $actor->getKey(),
            ]);

            $this->activities->create([
                'project_id' => $document->project_id,
                'actor_id' => $actor->getKey(),
                'event' => 'requirements.published',
                'subject_type' => RequirementsDocument::class,
                'subject_id' => $document->getKey(),
                'properties' => ['version' => $document->version],
            ]);

            return $document;
        });
    }
}
