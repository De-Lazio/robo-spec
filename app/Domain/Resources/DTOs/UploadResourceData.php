<?php

namespace App\Domain\Resources\DTOs;

use App\Domain\Resources\Enums\ResourceCategory;
use Illuminate\Http\UploadedFile;

final class UploadResourceData
{
    public function __construct(
        public readonly UploadedFile $file,
        public readonly ResourceCategory $category,
        public readonly ?string $description,
        public readonly ?string $folder = null,
        public readonly ?string $algorithmDiagramId = null,
    ) {}
}
