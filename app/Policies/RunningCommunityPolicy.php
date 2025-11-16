<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RunningCommunity;
use Illuminate\Auth\Access\HandlesAuthorization;

class RunningCommunityPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RunningCommunity');
    }

    public function view(AuthUser $authUser, RunningCommunity $runningCommunity): bool
    {
        return $authUser->can('View:RunningCommunity');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RunningCommunity');
    }

    public function update(AuthUser $authUser, RunningCommunity $runningCommunity): bool
    {
        return $authUser->can('Update:RunningCommunity');
    }

    public function delete(AuthUser $authUser, RunningCommunity $runningCommunity): bool
    {
        return $authUser->can('Delete:RunningCommunity');
    }

    public function restore(AuthUser $authUser, RunningCommunity $runningCommunity): bool
    {
        return $authUser->can('Restore:RunningCommunity');
    }

    public function forceDelete(AuthUser $authUser, RunningCommunity $runningCommunity): bool
    {
        return $authUser->can('ForceDelete:RunningCommunity');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RunningCommunity');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RunningCommunity');
    }

    public function replicate(AuthUser $authUser, RunningCommunity $runningCommunity): bool
    {
        return $authUser->can('Replicate:RunningCommunity');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RunningCommunity');
    }

}