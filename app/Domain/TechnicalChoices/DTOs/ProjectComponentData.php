<?php

namespace App\Domain\TechnicalChoices\DTOs;

readonly class ProjectComponentData
{
    /**
     * @param  list<string>  $linkedFunctionIds
     */
    public function __construct(
        public string $componentId,
        public int $quantity,
        public ?string $rationale,
        public array $linkedFunctionIds = [],
    ) {}
}
