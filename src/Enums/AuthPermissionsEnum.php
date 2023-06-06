<?php

namespace EscolaLms\Auth\Enums;

use EscolaLms\Core\Enums\BasicEnum;

class AuthPermissionsEnum extends BasicEnum
{
    const USER_MANAGE = 'user_manage';
    //
    const USER_CREATE         = 'user_create';
    const USER_DELETE         = 'user_delete';
    const USER_DELETE_SELF    = 'user_delete_self';
    const USER_UPDATE         = 'user_update';
    const USER_UPDATE_SELF    = 'user_update_self';
    const USER_READ           = 'user_read';
    const USER_READ_SELF      = 'user_read_self';
    const USER_READ_OWNED     = 'user_read_course-authored';
    const USER_LIST           = 'user_list';
    const USER_LIST_OWNED     = 'user_list_course-authored';
    const USER_VERIFY_ACCOUNT = 'user_verify_account';
    //
    const USER_GROUP_CREATE        = 'user-group_create';
    const USER_GROUP_DELETE        = 'user-group_delete';
    const USER_GROUP_LIST          = 'user-group_list';
    const USER_GROUP_LIST_SELF     = 'user-group_list_self'; // list groups Student belongs to / Tutor courses belong to
    const USER_GROUP_MEMBER_ADD    = 'user-group_member-add';
    const USER_GROUP_MEMBER_REMOVE = 'user-group_member-remove';
    const USER_GROUP_READ          = 'user-group_read';
    const USER_GROUP_READ_SELF     = 'user-group_read_self'; // get details about group Student belongs to / Tutor courses belong to
    const USER_GROUP_UPDATE        = 'user-group_update';
    //
    const USER_INTEREST_UPDATE      = 'user-interest_update';
    const USER_INTEREST_UPDATE_SELF = 'user-interest_update_self';
    //
    const USER_SETTING_UPDATE      = 'user-setting_update';
    const USER_SETTING_UPDATE_SELF = 'user-setting_update_self';

    const USER_IMPERSONATE = 'user_impersonate';
}
