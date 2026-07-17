<?php

namespace App\Domain\Projects\Contracts;

use App\Models\ProjectActivity;

interface ProjectActivityRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): ProjectActivity;
}
