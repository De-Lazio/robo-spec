<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Projects\Enums\ProjectStatus;
use App\Domain\Projects\Enums\RobotType;

readonly class UpdateProjectData
{
    /**
     * @param  list<string>  $tags
     */
    public function __construct(
        public string $name,
        public ?string $description,
        public RobotType $robotType,
        public ?string $domain,
        public ProjectStatus $status,
        public array $tags = [],
    ) {}
}
