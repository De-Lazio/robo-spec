<?php

namespace App\Domain\Projects\Repositories;

use App\Domain\Projects\Contracts\ProjectActivityRepositoryInterface;
use App\Models\ProjectActivity;

class EloquentProjectActivityRepository implements ProjectActivityRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): ProjectActivity
    {
        return ProjectActivity::query()->create($attributes);
    }
}
