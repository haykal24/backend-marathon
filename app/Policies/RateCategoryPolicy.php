<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RateCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class RateCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RateCategory');
    }

    public function view(AuthUser $authUser, RateCategory $rateCategory): bool
    {
        return $authUser->can('View:RateCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RateCategory');
    }

    public function update(AuthUser $authUser, RateCategory $rateCategory): bool
    {
        return $authUser->can('Update:RateCategory');
    }

    public function delete(AuthUser $authUser, RateCategory $rateCategory): bool
    {
        return $authUser->can('Delete:RateCategory');
    }

    public function restore(AuthUser $authUser, RateCategory $rateCategory): bool
    {
        return $authUser->can('Restore:RateCategory');
    }

    public function forceDelete(AuthUser $authUser, RateCategory $rateCategory): bool
    {
        return $authUser->can('ForceDelete:RateCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RateCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RateCategory');
    }

    public function replicate(AuthUser $authUser, RateCategory $rateCategory): bool
    {
        return $authUser->can('Replicate:RateCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RateCategory');
    }

}