<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BioLink;
use Illuminate\Auth\Access\HandlesAuthorization;

class BioLinkPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BioLink');
    }

    public function view(AuthUser $authUser, BioLink $bioLink): bool
    {
        return $authUser->can('View:BioLink');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BioLink');
    }

    public function update(AuthUser $authUser, BioLink $bioLink): bool
    {
        return $authUser->can('Update:BioLink');
    }

    public function delete(AuthUser $authUser, BioLink $bioLink): bool
    {
        return $authUser->can('Delete:BioLink');
    }

    public function restore(AuthUser $authUser, BioLink $bioLink): bool
    {
        return $authUser->can('Restore:BioLink');
    }

    public function forceDelete(AuthUser $authUser, BioLink $bioLink): bool
    {
        return $authUser->can('ForceDelete:BioLink');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BioLink');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BioLink');
    }

    public function replicate(AuthUser $authUser, BioLink $bioLink): bool
    {
        return $authUser->can('Replicate:BioLink');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BioLink');
    }

}