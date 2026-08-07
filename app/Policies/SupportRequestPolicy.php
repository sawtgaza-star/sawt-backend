<?php

namespace App\Policies;

use App\Models\SupportRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupportRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_support::request');
    }

    public function view(User $user, SupportRequest $supportRequest): bool
    {
        return $user->can('view_support::request');
    }

    public function create(User $user): bool
    {
        return $user->can('create_support::request');
    }

    public function update(User $user, SupportRequest $supportRequest): bool
    {
        return $user->can('update_support::request');
    }

    public function delete(User $user, SupportRequest $supportRequest): bool
    {
        return $user->can('delete_support::request');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_support::request');
    }

    public function forceDelete(User $user, SupportRequest $supportRequest): bool
    {
        return $user->can('force_delete_support::request');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_support::request');
    }

    public function restore(User $user, SupportRequest $supportRequest): bool
    {
        return $user->can('restore_support::request');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_support::request');
    }

    public function replicate(User $user, SupportRequest $supportRequest): bool
    {
        return $user->can('replicate_support::request');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_support::request');
    }
}
