<?php

namespace App\Policies;

use App\Enums\RoleType;
use App\Models\User;

class RolePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole(RoleType::SUPER_ADMIN->value);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user): bool
    {
        return $user->hasRole(RoleType::SUPER_ADMIN->value);
    }

    public function delete(User $user): bool
    {
        return false;
    }
}
