<?php

namespace App\Domain\Projects\Contracts;

use App\Models\ProjectMember;

interface ProjectMembershipRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): ProjectMember;
}
