<?php

namespace App\Policies;

use App\Models\SupportMethod;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupportMethodPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_support::method');
    }

    public function view(User $user, SupportMethod $supportMethod): bool
    {
        return $user->can('view_support::method');
    }

    public function create(User $user): bool
    {
        return $user->can('create_support::method');
    }

    public function update(User $user, SupportMethod $supportMethod): bool
    {
        return $user->can('update_support::method');
    }

    public function delete(User $user, SupportMethod $supportMethod): bool
    {
        return $user->can('delete_support::method');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_support::method');
    }

    public function forceDelete(User $user, SupportMethod $supportMethod): bool
    {
        return $user->can('force_delete_support::method');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_support::method');
    }

    public function restore(User $user, SupportMethod $supportMethod): bool
    {
        return $user->can('restore_support::method');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_support::method');
    }

    public function replicate(User $user, SupportMethod $supportMethod): bool
    {
        return $user->can('replicate_support::method');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_support::method');
    }
}
