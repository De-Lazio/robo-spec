<?php

namespace App\Domain\Organizations\DTOs;

readonly class CreateOrganizationData
{
    public function __construct(
        public string $name,
        public ?string $description,
    ) {}
}
