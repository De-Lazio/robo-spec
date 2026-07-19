<?php

namespace App\Domain\Requirements\Repositories;

use App\Domain\Requirements\Contracts\RequirementsDocumentRepositoryInterface;
use App\Domain\Requirements\Enums\RequirementsStatus;
use App\Models\RequirementsDocument;

class EloquentRequirementsDocumentRepository implements RequirementsDocumentRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): RequirementsDocument
    {
        return RequirementsDocument::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(RequirementsDocument $document, array $attributes): RequirementsDocument
    {
        $document->update($attributes);

        return $document;
    }

    public function latestForProject(string $projectId): ?RequirementsDocument
    {
        return RequirementsDocument::query()
            ->where('project_id', $projectId)
            ->orderByDesc('version')
            ->first();
    }

    public function activeDraftForProject(string $projectId): ?RequirementsDocument
    {
        return RequirementsDocument::query()
            ->where('project_id', $projectId)
            ->where('status', RequirementsStatus::Draft)
            ->orderByDesc('version')
            ->first();
    }

    public function latestPublishedForProject(string $projectId): ?RequirementsDocument
    {
        return RequirementsDocument::query()
            ->where('project_id', $projectId)
            ->where('status', RequirementsStatus::Published)
            ->orderByDesc('version')
            ->first();
    }
}
