<?php

namespace App\Domain\Components\Contracts;

use App\Models\ComponentCategory;
use Illuminate\Database\Eloquent\Collection;

interface ComponentCategoryRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): ComponentCategory;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(ComponentCategory $category, array $attributes): ComponentCategory;

    public function delete(ComponentCategory $category): void;

    /**
     * @return Collection<int, ComponentCategory>
     */
    public function all(): Collection;
}
