<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RatePlacement;
use Illuminate\Auth\Access\HandlesAuthorization;

class RatePlacementPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RatePlacement');
    }

    public function view(AuthUser $authUser, RatePlacement $ratePlacement): bool
    {
        return $authUser->can('View:RatePlacement');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RatePlacement');
    }

    public function update(AuthUser $authUser, RatePlacement $ratePlacement): bool
    {
        return $authUser->can('Update:RatePlacement');
    }

    public function delete(AuthUser $authUser, RatePlacement $ratePlacement): bool
    {
        return $authUser->can('Delete:RatePlacement');
    }

    public function restore(AuthUser $authUser, RatePlacement $ratePlacement): bool
    {
        return $authUser->can('Restore:RatePlacement');
    }

    public function forceDelete(AuthUser $authUser, RatePlacement $ratePlacement): bool
    {
        return $authUser->can('ForceDelete:RatePlacement');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RatePlacement');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RatePlacement');
    }

    public function replicate(AuthUser $authUser, RatePlacement $ratePlacement): bool
    {
        return $authUser->can('Replicate:RatePlacement');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RatePlacement');
    }

}