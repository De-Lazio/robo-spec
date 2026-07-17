<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Projects\Enums\RobotType;

readonly class CreateProjectData
{
    /**
     * @param  list<string>  $tags
     */
    public function __construct(
        public string $name,
        public ?string $description,
        public RobotType $robotType,
        public ?string $domain,
        public array $tags = [],
    ) {}
}
