<?php

namespace App\Http\Controllers;

use App\Domain\Export\DTOs\ExportSelectionDTO;
use App\Domain\Export\Services\ProjectExportService;
use App\Domain\Notifications\Services\NotificationService;
use App\Domain\Projects\Contracts\ProjectActivityRepositoryInterface;
use App\Http\Requests\ExportProjectRequest;
use App\Models\Project;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ProjectExportController extends Controller
{
    public function __construct(
        private readonly ProjectExportService $exportService,
        private readonly ProjectActivityRepositoryInterface $activities,
        private readonly NotificationService $notifications,
    ) {}

    public function store(ExportProjectRequest $request, Project $project): Response
    {
        $sections = $request->validated('sections') ?? [];

        $selection = new ExportSelectionDTO(
            generalInfo: $sections['generalInfo'] ?? true,
            requirements: $sections['requirements'] ?? true,
            team: $sections['team'] ?? true,
            technicalChoices: $sections['technicalChoices'] ?? true,
            algorithmDiagrams: $sections['algorithmDiagrams'] ?? true,
            tasks: $sections['tasks'] ?? true,
            resources: $sections['resources'] ?? true,
        );

        $pdf = $this->exportService->generate($project, $selection);

        $activity = $this->activities->create([
            'project_id' => $project->getKey(),
            'actor_id' => $request->user()->getKey(),
            'event' => 'export.generated',
            'subject_type' => Project::class,
            'subject_id' => $project->getKey(),
            'properties' => ['sections' => $selection->toArray()],
        ]);

        $this->notifications->notifyProjectEvent($activity);

        return $pdf->download(Str::slug($project->name).'-export.pdf');
    }
}
