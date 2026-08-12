<?php

namespace App\Policies;

use App\Models\SupportPlan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupportPlanPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_support::plan');
    }

    public function view(User $user, SupportPlan $supportPlan): bool
    {
        return $user->can('view_support::plan');
    }

    public function create(User $user): bool
    {
        return $user->can('create_support::plan');
    }

    public function update(User $user, SupportPlan $supportPlan): bool
    {
        return $user->can('update_support::plan');
    }

    public function delete(User $user, SupportPlan $supportPlan): bool
    {
        return $user->can('delete_support::plan');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_support::plan');
    }

    public function forceDelete(User $user, SupportPlan $supportPlan): bool
    {
        return $user->can('force_delete_support::plan');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_support::plan');
    }

    public function restore(User $user, SupportPlan $supportPlan): bool
    {
        return $user->can('restore_support::plan');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_support::plan');
    }

    public function replicate(User $user, SupportPlan $supportPlan): bool
    {
        return $user->can('replicate_support::plan');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_support::plan');
    }
}
