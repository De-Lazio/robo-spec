<?php

namespace App\Domain\Export\DTOs;

final class ExportSelectionDTO
{
    public function __construct(
        public readonly bool $generalInfo = true,
        public readonly bool $requirements = true,
        public readonly bool $team = true,
        public readonly bool $technicalChoices = true,
        public readonly bool $algorithmDiagrams = true,
        public readonly bool $tasks = true,
        public readonly bool $resources = true,
    ) {}

    /**
     * @return array<string, bool>
     */
    public function toArray(): array
    {
        return [
            'generalInfo' => $this->generalInfo,
            'requirements' => $this->requirements,
            'team' => $this->team,
            'technicalChoices' => $this->technicalChoices,
            'algorithmDiagrams' => $this->algorithmDiagrams,
            'tasks' => $this->tasks,
            'resources' => $this->resources,
        ];
    }
}
