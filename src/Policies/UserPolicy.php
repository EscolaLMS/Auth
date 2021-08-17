<?php

namespace EscolaLms\Auth\Policies;

use EscolaLms\Auth\Enums\AuthPermissionsEnum;
use EscolaLms\Core\Models\User as CoreUser;
use EscolaLms\Auth\Models\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(AuthPermissionsEnum::USER_LIST)
            || $user->can(AuthPermissionsEnum::USER_LIST_OWNED);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AuthUser $target): bool
    {
        return $user->can(AuthPermissionsEnum::USER_READ)
            || ($user->can(AuthPermissionsEnum::USER_READ_OWNED) && $this->isOwned($user, $target))
            || ($user->can(AuthPermissionsEnum::USER_READ_SELF) && $user->getKey() === $target->getKey());
    }

    private function isOwned(User $user, AuthUser $target): bool
    {
        // TODO: implement checking if tutor has access to target user data
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(AuthPermissionsEnum::USER_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AuthUser $target): bool
    {
        return $user->can(AuthPermissionsEnum::USER_UPDATE)
            || ($user->can(AuthPermissionsEnum::USER_UPDATE_SELF) && $user->getKey() === $target->getKey());
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AuthUser $target): bool
    {
        return $user->can(AuthPermissionsEnum::USER_DELETE)
            || ($user->can(AuthPermissionsEnum::USER_DELETE_SELF) && $user->getKey() === $target->getKey());
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AuthUser $target)
    {
        return $user->can(AuthPermissionsEnum::USER_MANAGE);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AuthUser $target)
    {
        return $user->can(AuthPermissionsEnum::USER_MANAGE);
    }


    public function updateInterests(User $user, AuthUser $target): bool
    {
        return $user->can(AuthPermissionsEnum::USER_INTEREST_UPDATE)
            || ($user->can(AuthPermissionsEnum::USER_INTEREST_UPDATE_SELF) && $user->getKey() === $target->getKey());
    }


    public function updateSettings(User $user, AuthUser $target): bool
    {
        return $user->can(AuthPermissionsEnum::USER_SETTING_UPDATE)
            || ($user->can(AuthPermissionsEnum::USER_SETTING_UPDATE_SELF) && $user->getKey() === $target->getKey());
    }
}
