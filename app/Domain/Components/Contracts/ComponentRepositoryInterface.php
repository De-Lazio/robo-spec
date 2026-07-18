<?php

namespace App\Domain\Components\Contracts;

use App\Models\Component;
use Illuminate\Database\Eloquent\Collection;

interface ComponentRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Component;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Component $component, array $attributes): Component;

    public function delete(Component $component): void;

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Component>
     */
    public function search(array $filters = []): Collection;
}
