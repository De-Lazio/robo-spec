<?php

namespace App\Policies;

use App\Models\ComponentCategory;
use App\Models\User;

class ComponentCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ComponentCategory $category): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_platform_admin;
    }

    public function update(User $user, ComponentCategory $category): bool
    {
        return (bool) $user->is_platform_admin;
    }

    public function delete(User $user, ComponentCategory $category): bool
    {
        return (bool) $user->is_platform_admin;
    }
}
