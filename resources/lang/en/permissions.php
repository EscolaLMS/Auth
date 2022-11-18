<?php

use EscolaLms\Auth\Enums\AuthPermissionsEnum;

return [
    AuthPermissionsEnum::USER_MANAGE => 'Manage user',
    AuthPermissionsEnum::USER_CREATE => 'Create user',
    AuthPermissionsEnum::USER_DELETE => 'Delete user',
    AuthPermissionsEnum::USER_DELETE_SELF => 'Delete self',
    AuthPermissionsEnum::USER_UPDATE => 'Update user',
    AuthPermissionsEnum::USER_UPDATE_SELF => 'Update self',
    AuthPermissionsEnum::USER_READ => 'Read user',
    AuthPermissionsEnum::USER_READ_SELF => 'Read self user',
    AuthPermissionsEnum::USER_READ_OWNED => 'Read owned user',
    AuthPermissionsEnum::USER_LIST => 'User list',
    AuthPermissionsEnum::USER_LIST_OWNED => 'User owned list',
    AuthPermissionsEnum::USER_VERIFY_ACCOUNT => 'Verify account',
    AuthPermissionsEnum::USER_GROUP_CREATE => 'Create group',
    AuthPermissionsEnum::USER_GROUP_DELETE => 'Delete group',
    AuthPermissionsEnum::USER_GROUP_LIST => 'Group list',
    AuthPermissionsEnum::USER_GROUP_LIST_SELF => 'Group list self',
    AuthPermissionsEnum::USER_GROUP_MEMBER_ADD => 'Add group member',
    AuthPermissionsEnum::USER_GROUP_MEMBER_REMOVE => 'Remove group member',
    AuthPermissionsEnum::USER_GROUP_READ => 'Read group',
    AuthPermissionsEnum::USER_GROUP_READ_SELF => 'Read self group',
    AuthPermissionsEnum::USER_GROUP_UPDATE => 'Update group',
    AuthPermissionsEnum::USER_INTEREST_UPDATE => 'Update interest',
    AuthPermissionsEnum::USER_INTEREST_UPDATE_SELF => 'Update self interest',
    AuthPermissionsEnum::USER_SETTING_UPDATE => 'Update setting',
    AuthPermissionsEnum::USER_SETTING_UPDATE_SELF => 'Update self setting',
];