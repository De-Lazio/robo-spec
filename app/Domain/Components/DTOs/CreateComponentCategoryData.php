<?php

namespace App\Domain\Components\DTOs;

use App\Domain\Components\Enums\ComponentType;

readonly class CreateComponentCategoryData
{
    public function __construct(
        public ComponentType $type,
        public string $name,
    ) {}
}
