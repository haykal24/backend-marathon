<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\FAQ;
use Illuminate\Auth\Access\HandlesAuthorization;

class FAQPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FAQ');
    }

    public function view(AuthUser $authUser, FAQ $fAQ): bool
    {
        return $authUser->can('View:FAQ');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:FAQ');
    }

    public function update(AuthUser $authUser, FAQ $fAQ): bool
    {
        return $authUser->can('Update:FAQ');
    }

    public function delete(AuthUser $authUser, FAQ $fAQ): bool
    {
        return $authUser->can('Delete:FAQ');
    }

    public function restore(AuthUser $authUser, FAQ $fAQ): bool
    {
        return $authUser->can('Restore:FAQ');
    }

    public function forceDelete(AuthUser $authUser, FAQ $fAQ): bool
    {
        return $authUser->can('ForceDelete:FAQ');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:FAQ');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:FAQ');
    }

    public function replicate(AuthUser $authUser, FAQ $fAQ): bool
    {
        return $authUser->can('Replicate:FAQ');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:FAQ');
    }

}