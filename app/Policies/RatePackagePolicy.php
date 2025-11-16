<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RatePackage;
use Illuminate\Auth\Access\HandlesAuthorization;

class RatePackagePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RatePackage');
    }

    public function view(AuthUser $authUser, RatePackage $ratePackage): bool
    {
        return $authUser->can('View:RatePackage');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RatePackage');
    }

    public function update(AuthUser $authUser, RatePackage $ratePackage): bool
    {
        return $authUser->can('Update:RatePackage');
    }

    public function delete(AuthUser $authUser, RatePackage $ratePackage): bool
    {
        return $authUser->can('Delete:RatePackage');
    }

    public function restore(AuthUser $authUser, RatePackage $ratePackage): bool
    {
        return $authUser->can('Restore:RatePackage');
    }

    public function forceDelete(AuthUser $authUser, RatePackage $ratePackage): bool
    {
        return $authUser->can('ForceDelete:RatePackage');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RatePackage');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RatePackage');
    }

    public function replicate(AuthUser $authUser, RatePackage $ratePackage): bool
    {
        return $authUser->can('Replicate:RatePackage');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RatePackage');
    }

}