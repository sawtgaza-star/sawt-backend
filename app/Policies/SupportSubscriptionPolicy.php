<?php

namespace App\Policies;

use App\Models\SupportSubscription;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupportSubscriptionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_support::subscription');
    }

    public function view(User $user, SupportSubscription $supportSubscription): bool
    {
        return $user->can('view_support::subscription');
    }

    public function create(User $user): bool
    {
        return $user->can('create_support::subscription');
    }

    public function update(User $user, SupportSubscription $supportSubscription): bool
    {
        return $user->can('update_support::subscription');
    }

    public function delete(User $user, SupportSubscription $supportSubscription): bool
    {
        return $user->can('delete_support::subscription');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_support::subscription');
    }

    public function forceDelete(User $user, SupportSubscription $supportSubscription): bool
    {
        return $user->can('force_delete_support::subscription');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_support::subscription');
    }

    public function restore(User $user, SupportSubscription $supportSubscription): bool
    {
        return $user->can('restore_support::subscription');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_support::subscription');
    }

    public function replicate(User $user, SupportSubscription $supportSubscription): bool
    {
        return $user->can('replicate_support::subscription');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_support::subscription');
    }
}
