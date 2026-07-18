<?php

namespace App\Policies;

use App\Models\Component;
use App\Models\User;

class ComponentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Component $component): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_platform_admin;
    }

    public function update(User $user, Component $component): bool
    {
        return (bool) $user->is_platform_admin;
    }

    public function delete(User $user, Component $component): bool
    {
        return (bool) $user->is_platform_admin;
    }
}
