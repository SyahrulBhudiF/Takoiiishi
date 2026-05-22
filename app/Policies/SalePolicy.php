<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Sale;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Sale');
    }

    public function view(AuthUser $authUser, Sale $sale): bool
    {
        return $authUser->can('View:Sale');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Sale');
    }

    public function update(AuthUser $authUser, Sale $sale): bool
    {
        if (! $authUser->can('Update:Sale')) {
            return false;
        }

        if (UserRole::parse($authUser->role) === UserRole::KaryawanOutlet) {
            return $sale->created_by === $authUser->id;
        }

        return true;
    }

    public function delete(AuthUser $authUser, Sale $sale): bool
    {
        if (! $authUser->can('Delete:Sale')) {
            return false;
        }

        if (UserRole::parse($authUser->role) === UserRole::KaryawanOutlet) {
            return $sale->created_by === $authUser->id;
        }

        return true;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return UserRole::parse($authUser->role) !== UserRole::KaryawanOutlet
            && $authUser->can('DeleteAny:Sale');
    }

    public function restore(AuthUser $authUser, Sale $sale): bool
    {
        return $authUser->can('Restore:Sale');
    }

    public function forceDelete(AuthUser $authUser, Sale $sale): bool
    {
        return $authUser->can('ForceDelete:Sale');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Sale');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Sale');
    }

    public function replicate(AuthUser $authUser, Sale $sale): bool
    {
        return $authUser->can('Replicate:Sale');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Sale');
    }

}