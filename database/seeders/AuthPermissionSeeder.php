<?php

namespace EscolaLms\Auth\Database\Seeders;

use EscolaLms\Auth\Enums\AuthPermissionsEnum;
use EscolaLms\Core\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthPermissionSeeder extends Seeder
{
    public function run()
    {
        $admin   = Role::findOrCreate(UserRole::ADMIN, 'api');
        $tutor   = Role::findOrCreate(UserRole::TUTOR, 'api');
        $student =  Role::findOrCreate(UserRole::STUDENT, 'api');

        foreach (AuthPermissionsEnum::asArray() as $const => $value) {
            Permission::findOrCreate($value, 'api');
        }

        $admin->givePermissionTo([
            AuthPermissionsEnum::USER_CREATE,
            AuthPermissionsEnum::USER_DELETE,
            AuthPermissionsEnum::USER_GROUP_CREATE,
            AuthPermissionsEnum::USER_GROUP_DELETE,
            AuthPermissionsEnum::USER_GROUP_LIST,
            AuthPermissionsEnum::USER_GROUP_MEMBER_ADD,
            AuthPermissionsEnum::USER_GROUP_MEMBER_REMOVE,
            AuthPermissionsEnum::USER_GROUP_READ,
            AuthPermissionsEnum::USER_GROUP_UPDATE,
            AuthPermissionsEnum::USER_INTEREST_UPDATE,
            AuthPermissionsEnum::USER_LIST,
            AuthPermissionsEnum::USER_READ,
            AuthPermissionsEnum::USER_SETTING_UPDATE,
            AuthPermissionsEnum::USER_UPDATE,
            AuthPermissionsEnum::USER_VERIFY_ACCOUNT,
            AuthPermissionsEnum::USER_GROUP_LIST_SELF,
            AuthPermissionsEnum::USER_GROUP_READ_SELF,
            AuthPermissionsEnum::USER_INTEREST_UPDATE_SELF,
            AuthPermissionsEnum::USER_LIST_OWNED,
            AuthPermissionsEnum::USER_READ_OWNED,
            AuthPermissionsEnum::USER_READ_SELF,
            AuthPermissionsEnum::USER_SETTING_UPDATE_SELF,
            AuthPermissionsEnum::USER_UPDATE_SELF,
            AuthPermissionsEnum::USER_IMPERSONATE,
        ]);
        $tutor->givePermissionTo([
            AuthPermissionsEnum::USER_GROUP_LIST_SELF,
            AuthPermissionsEnum::USER_GROUP_READ_SELF,
            AuthPermissionsEnum::USER_INTEREST_UPDATE_SELF,
            AuthPermissionsEnum::USER_LIST_OWNED,
            AuthPermissionsEnum::USER_READ_OWNED,
            AuthPermissionsEnum::USER_READ_SELF,
            AuthPermissionsEnum::USER_SETTING_UPDATE_SELF,
            AuthPermissionsEnum::USER_UPDATE_SELF,
        ]);
        $student->givePermissionTo([
            AuthPermissionsEnum::USER_GROUP_LIST_SELF,
            AuthPermissionsEnum::USER_GROUP_READ_SELF,
            AuthPermissionsEnum::USER_INTEREST_UPDATE_SELF,
            AuthPermissionsEnum::USER_READ_SELF,
            AuthPermissionsEnum::USER_SETTING_UPDATE_SELF,
            AuthPermissionsEnum::USER_UPDATE_SELF,
        ]);
    }
}
