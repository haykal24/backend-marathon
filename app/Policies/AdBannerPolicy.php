<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AdBanner;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdBannerPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AdBanner');
    }

    public function view(AuthUser $authUser, AdBanner $adBanner): bool
    {
        return $authUser->can('View:AdBanner');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AdBanner');
    }

    public function update(AuthUser $authUser, AdBanner $adBanner): bool
    {
        return $authUser->can('Update:AdBanner');
    }

    public function delete(AuthUser $authUser, AdBanner $adBanner): bool
    {
        return $authUser->can('Delete:AdBanner');
    }

    public function restore(AuthUser $authUser, AdBanner $adBanner): bool
    {
        return $authUser->can('Restore:AdBanner');
    }

    public function forceDelete(AuthUser $authUser, AdBanner $adBanner): bool
    {
        return $authUser->can('ForceDelete:AdBanner');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AdBanner');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AdBanner');
    }

    public function replicate(AuthUser $authUser, AdBanner $adBanner): bool
    {
        return $authUser->can('Replicate:AdBanner');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AdBanner');
    }

}