<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SiteSetting;
use Illuminate\Auth\Access\HandlesAuthorization;

class SiteSettingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SiteSetting');
    }

    public function view(AuthUser $authUser, SiteSetting $siteSetting): bool
    {
        return $authUser->can('View:SiteSetting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SiteSetting');
    }

    public function update(AuthUser $authUser, SiteSetting $siteSetting): bool
    {
        return $authUser->can('Update:SiteSetting');
    }

    public function delete(AuthUser $authUser, SiteSetting $siteSetting): bool
    {
        return $authUser->can('Delete:SiteSetting');
    }

    public function restore(AuthUser $authUser, SiteSetting $siteSetting): bool
    {
        return $authUser->can('Restore:SiteSetting');
    }

    public function forceDelete(AuthUser $authUser, SiteSetting $siteSetting): bool
    {
        return $authUser->can('ForceDelete:SiteSetting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SiteSetting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SiteSetting');
    }

    public function replicate(AuthUser $authUser, SiteSetting $siteSetting): bool
    {
        return $authUser->can('Replicate:SiteSetting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SiteSetting');
    }

}