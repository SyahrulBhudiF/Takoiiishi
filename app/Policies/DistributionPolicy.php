<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Distribution;
use Illuminate\Auth\Access\HandlesAuthorization;

class DistributionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Distribution');
    }

    public function view(AuthUser $authUser, Distribution $distribution): bool
    {
        return $authUser->can('View:Distribution');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Distribution');
    }

    public function update(AuthUser $authUser, Distribution $distribution): bool
    {
        return $authUser->can('Update:Distribution');
    }

    public function delete(AuthUser $authUser, Distribution $distribution): bool
    {
        return $authUser->can('Delete:Distribution');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Distribution');
    }

    public function restore(AuthUser $authUser, Distribution $distribution): bool
    {
        return $authUser->can('Restore:Distribution');
    }

    public function forceDelete(AuthUser $authUser, Distribution $distribution): bool
    {
        return $authUser->can('ForceDelete:Distribution');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Distribution');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Distribution');
    }

    public function replicate(AuthUser $authUser, Distribution $distribution): bool
    {
        return $authUser->can('Replicate:Distribution');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Distribution');
    }

}