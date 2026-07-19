<?php

namespace App\Domain\Requirements\Contracts;

use App\Models\RequirementsDocument;

interface RequirementsDocumentRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): RequirementsDocument;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(RequirementsDocument $document, array $attributes): RequirementsDocument;

    public function latestForProject(string $projectId): ?RequirementsDocument;

    public function activeDraftForProject(string $projectId): ?RequirementsDocument;

    public function latestPublishedForProject(string $projectId): ?RequirementsDocument;
}
