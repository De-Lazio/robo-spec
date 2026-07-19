<?php

namespace App\Domain\AlgorithmDiagrams\DTOs;

readonly class AlgorithmDiagramData
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public string $name,
        public array $data,
        public ?int $microcontrollerComponentId = null,
        public ?int $energySourceComponentId = null,
    ) {}
}
