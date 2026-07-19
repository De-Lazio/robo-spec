<?php

namespace App\Http\Controllers;

use App\Domain\Requirements\Rules\RequirementsStepRules;
use App\Domain\Requirements\Services\RequirementsService;
use App\Http\Requests\SaveRequirementsDraftRequest;
use App\Http\Requests\SaveRequirementsStepRequest;
use App\Models\Project;
use App\Models\RequirementsDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RequirementsDocumentController extends Controller
{
    public function __construct(
        private readonly RequirementsService $requirements,
    ) {}

    public function show(Project $project): Response
    {
        $this->authorize('viewRequirements', $project);

        $document = $this->requirements->currentForDisplay($project);

        return Inertia::render('Projects/Requirements/Show', [
            'project' => ['id' => $project->id, 'name' => $project->name],
            'requirementsDocument' => $document ? $this->readPayload($document) : null,
        ]);
    }

    public function edit(Request $request, Project $project): Response
    {
        $this->authorize('editRequirements', $project);

        $document = $this->requirements->getOrCreateDraft($project, $request->user());

        return Inertia::render('Projects/Requirements/Edit', [
            'project' => ['id' => $project->id, 'name' => $project->name],
            'requirementsDocument' => [
                'data' => $document->data,
                'currentStep' => $document->current_step,
                'version' => $document->version,
            ],
            'completedSteps' => $this->requirements->completedSteps($document),
            'options' => [
                'userOptions' => RequirementsStepRules::USER_OPTIONS,
                'safetyConstraints' => RequirementsStepRules::SAFETY_CONSTRAINTS,
            ],
        ]);
    }

    public function saveStep(SaveRequirementsStepRequest $request, Project $project, int $step): RedirectResponse
    {
        $document = $this->requirements->getOrCreateDraft($project, $request->user());
        $this->requirements->saveStep($document, $step, $request->validated(), $request->user());

        return back()->with('success', 'Étape enregistrée.');
    }

    public function saveDraft(SaveRequirementsDraftRequest $request, Project $project): RedirectResponse
    {
        $document = $this->requirements->getOrCreateDraft($project, $request->user());
        $this->requirements->saveDraft($document, $request->validated('data'), $request->user());

        return back()->with('success', 'Brouillon enregistré.');
    }

    public function publish(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('publishRequirements', $project);

        $document = $this->requirements->getOrCreateDraft($project, $request->user());
        $this->requirements->publish($document, $request->user());

        return to_route('projects.requirements.show', $project)->with('success', 'Cahier des charges publié.');
    }

    /**
     * @return array<string, mixed>
     */
    private function readPayload(RequirementsDocument $document): array
    {
        return [
            'data' => $document->data,
            'version' => $document->version,
            'publishedAt' => $document->published_at?->toDateTimeString(),
        ];
    }
}
