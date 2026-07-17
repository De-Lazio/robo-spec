<?php

namespace App\Domain\Projects\Repositories;

use App\Domain\Projects\Contracts\ProjectMembershipRepositoryInterface;
use App\Models\ProjectMember;

class EloquentProjectMembershipRepository implements ProjectMembershipRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): ProjectMember
    {
        return ProjectMember::query()->create($attributes);
    }
}
