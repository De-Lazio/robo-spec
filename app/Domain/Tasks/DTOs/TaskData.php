<?php

namespace App\Domain\Tasks\DTOs;

readonly class TaskData
{
    /**
     * @param  list<string>  $linkedFunctionIds
     */
    public function __construct(
        public string $title,
        public ?string $description,
        public ?int $assigneeId,
        public ?string $dueDate,
        public array $linkedFunctionIds = [],
    ) {}
}
