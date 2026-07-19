<?php

namespace App\Domain\Export\Services;

use App\Domain\AlgorithmDiagrams\Contracts\AlgorithmDiagramRepositoryInterface;
use App\Domain\Export\DTOs\ExportSelectionDTO;
use App\Domain\Export\Support\ExportLabels;
use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Domain\Requirements\Contracts\RequirementsDocumentRepositoryInterface;
use App\Domain\Resources\Contracts\ResourceRepositoryInterface;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Domain\TechnicalChoices\Contracts\ProjectComponentRepositoryInterface;
use App\Models\AlgorithmDiagram;
use App\Models\Project;
use App\Models\ProjectComponent;
use App\Models\Resource;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocument;
use Illuminate\Support\Facades\Storage;

class ProjectExportService
{
    public function __construct(
        private readonly RequirementsDocumentRepositoryInterface $requirementsDocuments,
        private readonly ProjectComponentRepositoryInterface $technicalChoices,
        private readonly AlgorithmDiagramRepositoryInterface $algorithmDiagrams,
        private readonly TaskRepositoryInterface $tasks,
        private readonly ResourceRepositoryInterface $resources,
    ) {}

    public function generate(Project $project, ExportSelectionDTO $selection): PdfDocument
    {
        $project->loadMissing(['owner', 'members.user']);

        $technicalChoices = $selection->technicalChoices
            ? $this->technicalChoices->forProject($project->getKey())
            : collect();

        return Pdf::loadView('exports.project', [
            'project' => $project,
            'sections' => $selection->toArray(),
            'requirementsDocument' => $selection->requirements
                ? $this->requirementsDocuments->latestPublishedForProject($project->getKey())
                : null,
            'members' => $selection->team ? $this->teamRoster($project) : [],
            'technicalChoices' => $technicalChoices,
            'technicalChoicesTotalCents' => $technicalChoices->sum(
                fn (ProjectComponent $choice): int => ($choice->component->price_cents ?? 0) * $choice->quantity,
            ),
            'diagrams' => $selection->algorithmDiagrams ? $this->diagramPayloads($project) : [],
            'tasks' => $selection->tasks ? $this->tasks->forProject($project->getKey()) : collect(),
            'resources' => $selection->resources ? $this->resources->forProject($project->getKey()) : collect(),
        ]);
    }

    /**
     * @return list<array{name: string, email: string, role: string, joined_at: string}>
     */
    private function teamRoster(Project $project): array
    {
        return $project->allMemberUsers()
            ->map(function (User $user) use ($project): array {
                $member = $project->members->firstWhere('user_id', $user->getKey());
                $role = $member?->role ?? ProjectMemberRole::Owner;
                $joinedAt = $member?->joined_at ?? $project->created_at;

                return [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => ExportLabels::role($role),
                    'joined_at' => $joinedAt->format('d/m/Y'),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array{diagram: AlgorithmDiagram, imageDataUri: ?string}>
     */
    private function diagramPayloads(Project $project): array
    {
        return $this->algorithmDiagrams->forProject($project->getKey())
            ->map(fn (AlgorithmDiagram $diagram): array => [
                'diagram' => $diagram,
                'imageDataUri' => $this->latestDiagramImage($diagram),
            ])
            ->values()
            ->all();
    }

    private function latestDiagramImage(AlgorithmDiagram $diagram): ?string
    {
        $resource = Resource::query()
            ->where('algorithm_diagram_id', $diagram->getKey())
            ->latest()
            ->first();

        if ($resource === null || ! Storage::disk($resource->disk)->exists($resource->path)) {
            return null;
        }

        $contents = Storage::disk($resource->disk)->get($resource->path);

        return 'data:'.$resource->mime_type.';base64,'.base64_encode($contents);
    }
}
