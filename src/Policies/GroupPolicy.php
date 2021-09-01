<?php

namespace EscolaLms\Auth\Policies;

use EscolaLms\Auth\Enums\AuthPermissionsEnum;
use EscolaLms\Auth\Models\Group;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User;

class GroupPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can(AuthPermissionsEnum::USER_GROUP_LIST)
            || $user->can(AuthPermissionsEnum::USER_GROUP_LIST_SELF);
    }

    public function view(User $user, Group $group): bool
    {
        return $user->can(AuthPermissionsEnum::USER_GROUP_READ)
            || ($user->can(AuthPermissionsEnum::USER_GROUP_READ_SELF) && $group->belongsToUser($user));
    }

    public function create(User $user): bool
    {
        return $user->can(AuthPermissionsEnum::USER_GROUP_CREATE);
    }

    public function update(User $user, Group $group): bool
    {
        return $user->can(AuthPermissionsEnum::USER_GROUP_UPDATE);
    }

    public function delete(User $user, Group $group): bool
    {
        return $user->can(AuthPermissionsEnum::USER_GROUP_DELETE);
    }

    public function restore(User $user, Group $group): bool
    {
        return $user->can(AuthPermissionsEnum::USER_GROUP_DELETE);
    }

    public function forceDelete(User $user, Group $group): bool
    {
        return $user->can(AuthPermissionsEnum::USER_GROUP_DELETE);
    }

    public function addMember(User $user, Group $group): bool
    {
        return $user->can(AuthPermissionsEnum::USER_GROUP_MEMBER_ADD);
    }

    public function removeMember(User $user, Group $group): bool
    {
        return $user->can(AuthPermissionsEnum::USER_GROUP_MEMBER_REMOVE);
    }
}
