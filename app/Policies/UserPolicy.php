<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if ($user->can('view_any_user')) {
            return true;
        }

        if (session()->has('impersonate')) {
            return true;
        }

        return false;
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('view_user');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_user');
    }

    public function update(AuthUser $authUser, User $model): bool
    {
        return $authUser->can('update_user') && $authUser->id !== $model->id;
    }

    public function delete(AuthUser $authUser, User $model): bool
    {
        return $authUser->can('delete_user') && $authUser->id !== $model->id;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any_user');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('restore_user');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_user');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_user');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_user');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('replicate_user');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_user');
    }
}
