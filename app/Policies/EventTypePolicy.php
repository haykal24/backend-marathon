<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EventType;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventTypePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EventType');
    }

    public function view(AuthUser $authUser, EventType $eventType): bool
    {
        return $authUser->can('View:EventType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EventType');
    }

    public function update(AuthUser $authUser, EventType $eventType): bool
    {
        return $authUser->can('Update:EventType');
    }

    public function delete(AuthUser $authUser, EventType $eventType): bool
    {
        return $authUser->can('Delete:EventType');
    }

    public function restore(AuthUser $authUser, EventType $eventType): bool
    {
        return $authUser->can('Restore:EventType');
    }

    public function forceDelete(AuthUser $authUser, EventType $eventType): bool
    {
        return $authUser->can('ForceDelete:EventType');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EventType');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EventType');
    }

    public function replicate(AuthUser $authUser, EventType $eventType): bool
    {
        return $authUser->can('Replicate:EventType');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EventType');
    }

}